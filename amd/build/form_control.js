function selectChanged() {
    var form = document.getElementById('id_dropSelect').closest('form');
    var id = form.dropSelect.value.split('_')[0];
    if (!id || id == -1) {
        form.id.value = null;
    } else {
        var type = form.dropSelect.value.split('_')[1];
        form.string.value = form.dropSelect.options[form.dropSelect.selectedIndex].text;
        form.id.value = id;
        for (let i = 0; i < form.type.length; i++) {
            if (type == form.type[i].value) {
                form.type[i].checked = true;
            } else {
                form.type[i].checked = false;
            }
        }
    }
}
