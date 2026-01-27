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

				if ( $ordCol['orderable'] == 'true' ) {
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
				if ( $reqCol['searchable'] == 'true' ) {
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
				if ( $reqCol['searchable'] == 'true' && $str != '' ) {
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

	function ssp_sql_query ( $request, $table, &$bindings, &$where )
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
			$where = $filterWhere . " AND `status` = 1";
		} else {
			$where = "WHERE `status` = 1";
		}

		// Main query to actually get the data
		return "SELECT `".implode("`, `", $selectCol)."` FROM `$table` $where $order $limit";
	}
?>
