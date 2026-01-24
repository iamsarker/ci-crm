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

/**
 * File Upload Security Validation
 * Validates file size and type on client-side before upload
 */
function validateFileUpload(input) {
	// Get max file size from data attribute (in bytes)
	const maxSize = parseInt(input.getAttribute('data-max-size')) || 5242880; // Default 5MB
	const maxSizeMB = (maxSize / 1024 / 1024).toFixed(2);

	// Allowed file extensions
	const allowedExtensions = ['gif', 'jpg', 'jpeg', 'png', 'pdf', 'txt'];

	// Allowed MIME types
	const allowedMimeTypes = [
		'image/gif',
		'image/jpeg',
		'image/jpg',
		'image/png',
		'application/pdf',
		'text/plain'
	];

	const files = input.files;
	let hasError = false;
	let errorMessages = [];

	// Validate each selected file
	for (let i = 0; i < files.length; i++) {
		const file = files[i];

		// Check file size
		if (file.size > maxSize) {
			errorMessages.push(`File "${file.name}" exceeds maximum size of ${maxSizeMB}MB`);
			hasError = true;
			continue;
		}

		// Check file extension
		const fileName = file.name.toLowerCase();
		const extension = fileName.split('.').pop();

		if (!allowedExtensions.includes(extension)) {
			errorMessages.push(`File "${file.name}" has invalid extension. Allowed: ${allowedExtensions.join(', ')}`);
			hasError = true;
			continue;
		}

		// Check for double extensions (e.g., file.php.txt)
		const parts = fileName.split('.');
		if (parts.length > 2) {
			// Check if any part before the last extension is a dangerous extension
			const dangerousExts = ['php', 'phtml', 'php3', 'php4', 'php5', 'pl', 'py', 'jsp', 'asp', 'sh', 'cgi'];
			for (let j = 0; j < parts.length - 1; j++) {
				if (dangerousExts.includes(parts[j])) {
					errorMessages.push(`File "${file.name}" has suspicious double extension`);
					hasError = true;
					break;
				}
			}
		}

		// Check MIME type (if browser supports it)
		if (file.type && !allowedMimeTypes.includes(file.type)) {
			errorMessages.push(`File "${file.name}" has invalid type (${file.type})`);
			hasError = true;
		}
	}

	// If there are errors, show them and clear the input
	if (hasError) {
		alert('File Upload Error:\n\n' + errorMessages.join('\n'));
		input.value = ''; // Clear the file input
		return false;
	}

	return true;
}
