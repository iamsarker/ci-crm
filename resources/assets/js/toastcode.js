/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function toastError(txt){
    $.toast({
        heading: 'Error',
        text: txt,
        showHideTransition: 'fade',
        icon: 'error'
    });
}

function toastInfo(txt){
    $.toast({
        heading: 'Information',
        text: txt,
        showHideTransition: 'slide',
        icon: 'info'
    });
}

function toastWarning(txt){
    $.toast({
        heading: 'Warning',
        text: txt,
        showHideTransition: 'plain',
        icon: 'warning'
    });
}

function toastSuccess(txt){
    $.toast({
        heading: 'Success',
        text: txt,
        showHideTransition: 'slide',
        icon: 'success'
    });
}

