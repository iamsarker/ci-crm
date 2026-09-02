<?php

	function ssp_limit ( $request )
	{
		$limit = '';

		if ( isset($request['start']) && $request['length'] != -1 ) {
			$limit = "LIMIT ".intval($request['start']).", ".intval($request['length']);
		}

		return $limit;
	}

	function ssp_order ( $request )
	{
		$order = '';
		if ( isset($request['order']) && count($request['order']) ) {
			$orderBy = array();
			$colLen=count($request['order']);

			for ( $i=0; $i<$colLen ; $i++ ) {
				$columnIdx = intval($request['order'][$i]['column']);
				$ordCol = $request['columns'][$columnIdx];

				if ( $ordCol['orderable'] == 'true' || $ordCol['orderable'] === true ) {
					$dir = $request['order'][$i]['dir'] === 'asc' ? 'ASC' : 'DESC';
					$orderBy[] = '`'.$ordCol['data'].'` '.$dir;
				}
			}
			if ( count( $orderBy ) ) {
				$order = 'ORDER BY '.implode(', ', $orderBy);
			}
		}
		return $order;
	}

	function ssp_filter ( $request, &$bindings )
	{
		$globalSearch = array();
		$columnSearch = array();
		if ( isset($request['search']) && $request['search']['value'] != '' ) {
			$str = '%' . $request['search']['value'] . '%';
			$colLen=count($request['columns']);

			for ( $i=0; $i<$colLen ; $i++ ) {
				$reqCol = $request['columns'][$i];
				if ( $reqCol['searchable'] == 'true' || $reqCol['searchable'] === true ) {
					array_push($bindings, $str);
					$globalSearch[] = "`".$reqCol['data']."` LIKE ? ";
				}
			}
		}
		if ( isset( $request['columns'] ) ) {
			$colLen=count($request['columns']);
			for ( $i=0; $i<$colLen ; $i++ ) {
				$reqCol = $request['columns'][$i];
				$str = $reqCol['search']['value'];
				if ( ($reqCol['searchable'] == 'true' || $reqCol['searchable'] === true) && $str != '' ) {
					if ( is_numeric($str) ) {
						array_push($bindings, $str);
						$columnSearch[] = "`".$reqCol['data']."` = ? ";
					} else {
						array_push($bindings, '%' . $str . '%');
						$columnSearch[] = "`".$reqCol['data']."` LIKE ? ";
					}
				}
			}
		}

		$where = '';
		if ( count( $globalSearch ) ) {
			$where = '('.implode(' OR ', $globalSearch).')';
		}
		if ( count( $columnSearch ) ) {
			$where = $where === '' ?
				implode(' AND ', $columnSearch) :
				$where .' AND '. implode(' AND ', $columnSearch);
		}
		if ( $where !== '' ) {
			$where = 'WHERE '.$where;
		}
		return $where;
	}

	/**
	 * Reseller tenant restriction for a DataTable source table.
	 *
	 * Returns '' for platform staff (no restriction). For a reseller admin it
	 * returns a `col IN (ids)` fragment, or ' 1 = 0 ' for any table not in the
	 * map below.
	 *
	 * ⚠️ Unmapped tables deny rather than allow. A reseller should never reach
	 * an unmapped table — RequestGuard blocks those controllers — so arriving
	 * here means a capability was granted without a scope rule. Returning an
	 * empty result set is a loud, safe, debuggable failure; returning
	 * everything would be a silent cross-tenant leak.
	 *
	 * All 16 ssp_sql_query() call sites live in src/controllers/whmazadmin/, so
	 * this never touches the customer portal.
	 */
	function ssp_tenant_scope ( $table )
	{
		if ( ! function_exists('isResellerAdmin') || ! isResellerAdmin() ) return '';

		// table => column naming the owning company
		$scopeCol = array(
			'companies'      => 'id',              // the reseller row itself + its sub-customers
			'order_view'     => 'company_id',
			'invoice_view'   => 'company_id',
			'ticket_view'    => 'company_id',
			'api_key_view'   => 'company_id',
			'order_services' => 'company_id',
			'order_domains'  => 'company_id',
			// Added in later phases, alongside the schema that makes them ownable:
			// 'promo_codes' => 'company_id',   // Phase 4, needs promo_codes.company_id
		);

		if ( ! isset($scopeCol[$table]) ) {
			log_message('error', 'ssp_tenant_scope: no scope rule for table "' . $table
				. '" reached by reseller admin #' . getAdminId() . ' — denying.');
			return ' 1 = 0 ';
		}

		return adminScopeSql('`' . $scopeCol[$table] . '`');
	}

	function ssp_sql_query ( $request, $table, &$bindings, &$where, $extraWhere = '', $statusCond = " `status` = 1 " )
	{
		// Build the SQL query string from the request
		$limit = ssp_limit( $request );
		$order = ssp_order( $request );
		$filterWhere = ssp_filter( $request, $bindings );

		$selectCol = array();
		$colLen=count($request['columns']);
		for ( $i=0; $i<$colLen ; $i++ ) {
			array_push($selectCol, $request['columns'][$i]['data']);
		}

		// Add status filter
		if( $filterWhere !== '' ) {
			$where = $filterWhere . " AND " . $statusCond;
		} else {
			$where = "WHERE " . $statusCond;
		}

		// Add extra where conditions (e.g., soft delete filter)
		if ( $extraWhere !== '' ) {
			$where .= " AND " . $extraWhere;
		}

		// SECURITY: reseller tenant scope. Appended last and never merged into
		// $extraWhere, because callers pass their own (Email_template.php) and
		// overwriting theirs would drop a soft-delete filter.
		//
		// ⚠️ This is the ONLY place tenant scope may be applied to these lists.
		// Do NOT use the $tmpCompanyId trick in Order.php / Invoice.php: that
		// injects a filter into $request['columns'][i]['search']['value'], which
		// ssp_filter() reads straight from the query string, so a reseller can
		// simply overwrite it in the AJAX URL. It is a convenience filter, not
		// a security boundary.
		//
		// $where is by-reference, so countDataTableFilterRecords($where, ...)
		// inherits this automatically and the filtered COUNT stays scoped too.
		$tenantWhere = ssp_tenant_scope( $table );
		if ( $tenantWhere !== '' ) {
			$where .= " AND " . $tenantWhere;
		}

		// Main query to actually get the data
		return "SELECT `".implode("`, `", $selectCol)."` FROM `$table` $where $order $limit";
	}
?>
