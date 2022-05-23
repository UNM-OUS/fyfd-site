/* prepare element showing/hiding events for special accommodations fields */
document.addEventListener('DigraphDOMReady', (e) => {
    const fields = e.target.getElementsByClassName('regalia-almamater-field');
    for (const i in fields) {
        if (Object.hasOwnProperty.call(fields, i)) {
            const field = fields[i];
            field.addEventListener('change', e => update(field));
            update(field);
        }
    }
    function update(field) {
        const notFound = field.getElementsByClassName('regalia-almamater-field__not-found')[0].getElementsByTagName('input')[0];
        const institution = field.getElementsByClassName('regalia-almamater-field__institution')[0];
        if (!notFound.checked) {
            institution.style.display = null;
        } else {
            institution.style.display = 'none';
        }
    }
});