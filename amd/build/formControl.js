function selectChanged() {
    var form = document.getElementById('id_dropSelect').closest('form');
    var id = form.dropSelect.value.split('_')[0];
    if (!id || id == -1) {
        form.id.value = null;
    } else {
        var type =  form.dropSelect.value.split('_')[1];
        form.string.value = form.dropSelect.options[form.dropSelect.selectedIndex].text;
        form.id.value = id;
        let box0 = form.type[0];
        let box1 = form.type[1];
        if (type == box0.value) {
            box0.checked = true;
            box1.checked = false;
        } else {
            box0.checked = false;
            box1.checked = true;
        }
    }
}
