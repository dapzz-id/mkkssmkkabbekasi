function deleteConfirmation(e) {
    e.preventDefault();
    formToSubmit = e.target;
    $('.itemForDelete').text(` "${$(e.target).find('button[type="submit"]').data('confirm')}"`);
    $('.confirm-delete-card').show();
}

function confirmDelete() {
    formToSubmit.submit();
    $('.confirm-delete-card').hide();
}

function cancelDelete() {
    $('.confirm-delete-card').hide();
}