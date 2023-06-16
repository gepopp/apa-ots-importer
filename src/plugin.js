import axios from "axios";
import Alpine from 'alpinejs'

window.qs = require('qs');
window.axios = axios;
window.Alpine = Alpine

Alpine.start()

window.importer = () => {
    return {
        search: '',
        channel: '',
        after: '1970-01-01',
        before: new Date().toISOString().split('T')[0],
        picture: true,
        ots: [],
        error: false,
        loading: false,
        load_ots() {

            this.loading = true;

            axios.post(ajaxurl, qs.stringify({
                action: 'load_ots',
                search: this.search,
                channel: this.channel,
                picture: this.picture,
                before: this.before,
                after: this.after
            }))
                .then((rsp) => this.ots = rsp.data)
                .catch((error) => this.error = error.response.data.data.message)
                .then(() => this.loading = false );
        },
        importing: false,
        import_ots(id) {
            this.importing = id;
            axios.post(ajaxurl, qs.stringify({
                action: 'import_ots',
                id: id
            })).then((rsp) => {
                this.imported.push(id);
                this.importing = false;
            });
        },
        imported: imported
    }
}