function dtParamsFlatten(params) {
	params.columns.forEach(function (column, index) {
		params['columns[' + index + '].data'] = column.data;
		params['columns[' + index + '].name'] = column.name;
		params['columns[' + index + '].searchable'] = column.searchable;
		params['columns[' + index + '].orderable'] = column.orderable;
		params['columns[' + index + '].search.regex'] = column.search.regex;
		params['columns[' + index + '].search.value'] = column.search.value;
	});
	delete params.columns;

	params.order.forEach(function (order, index) {
		params['order[' + index + '].column'] = order.column;
		params['order[' + index + '].dir'] = order.dir;
	});
	delete params.order;

	params['search.regex'] = params.search.regex;
	params['search.value'] = params.search.value;
	delete params.search;

	return params;
}

$(".select2").select2();


function makeSlug(str){
	let slug = str.toLowerCase().replaceAll(" ","-").replaceAll(",","-").replaceAll(".","-").replaceAll("/", "-").replaceAll("'", "");
	return slug.replaceAll("----", "-").replaceAll("---", "-").replaceAll("!", "-").replaceAll("?", "-").replaceAll("--", "-");
}

$("input.make-slug").on("keyup", function (){
	let str = $(this).val();
	let slug = makeSlug(str);
	$("input#slug").val(slug);
});
