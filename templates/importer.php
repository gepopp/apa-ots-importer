<?php
global $wpdb;
$importiert = $wpdb->get_col( 'SELECT meta_value FROM wp_postmeta WHERE meta_key = "ots_id"' );
ob_start();
?>
<style>
    .centered {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .image-placeholder {
        width: 150px;
        aspect-ratio: 16 / 9;
        background-color: #efefef;

    }

    .placeholder {
        width: 100%;
        aspect-ratio: 16 / 9;
        background-color: #efefef;
    }

    .lds-spinner {
        color: official;
        display: inline-block;
        position: relative;
        width: 80px;
        height: 80px;
    }

    .lds-spinner div {
        transform-origin: 40px 40px;
        animation: lds-spinner 1.2s linear infinite;
    }

    .lds-spinner div:after {
        content: " ";
        display: block;
        position: absolute;
        top: 3px;
        left: 37px;
        width: 6px;
        height: 18px;
        border-radius: 20%;
        background: #F06900;
    }

    .lds-spinner div:nth-child(1) {
        transform: rotate(0deg);
        animation-delay: -1.1s;
    }

    .lds-spinner div:nth-child(2) {
        transform: rotate(30deg);
        animation-delay: -1s;
    }

    .lds-spinner div:nth-child(3) {
        transform: rotate(60deg);
        animation-delay: -0.9s;
    }

    .lds-spinner div:nth-child(4) {
        transform: rotate(90deg);
        animation-delay: -0.8s;
    }

    .lds-spinner div:nth-child(5) {
        transform: rotate(120deg);
        animation-delay: -0.7s;
    }

    .lds-spinner div:nth-child(6) {
        transform: rotate(150deg);
        animation-delay: -0.6s;
    }

    .lds-spinner div:nth-child(7) {
        transform: rotate(180deg);
        animation-delay: -0.5s;
    }

    .lds-spinner div:nth-child(8) {
        transform: rotate(210deg);
        animation-delay: -0.4s;
    }

    .lds-spinner div:nth-child(9) {
        transform: rotate(240deg);
        animation-delay: -0.3s;
    }

    .lds-spinner div:nth-child(10) {
        transform: rotate(270deg);
        animation-delay: -0.2s;
    }

    .lds-spinner div:nth-child(11) {
        transform: rotate(300deg);
        animation-delay: -0.1s;
    }

    .lds-spinner div:nth-child(12) {
        transform: rotate(330deg);
        animation-delay: 0s;
    }

    @keyframes lds-spinner {
        0% {
            opacity: 1;
        }
        100% {
            opacity: 0;
        }
    }
</style>
<script>
    const imported = <?php echo json_encode( $importiert ) ?>;
</script>

<div class="wrap">
    <h1>Neueste OTS Meldungen</h1>
    <div x-data="importer()" x-init="load_ots()">
        <div style="width: 100%; display: flex; justify-content: end">
            <div style="margin-right: 15px">
                <label style="display: block; width: 100%" for="post-search-input">Veröffentlicht nach:</label>
                <input type="date" x-model="after" x-on:change="load_ots()">
            </div>
            <div style="margin-right: 15px">
                <label style="display: block; width: 100%" for="post-search-input">Veröffentlicht vor:</label>
                <input type="date" x-model="before" x-on:change="load_ots()">
            </div>
            <div style="margin-right: 15px">
                <label style="display: block; width: 100%" for="post-search-input">Hat Bild</label>
                <input type="checkbox" x-model="picture" x-on:change="load_ots()">
            </div>
            <div style="margin-right: 15px">
                <label style="display: block; width: 100%" for="post-search-input">Kanal</label>
                <select x-model="channel" x-on:change="load_ots()">
                    <option>Bitte wählen...</option>
                    <option value="politik">Politik</option>
                    <option value="wirtschaft">Wirtschaft</option>
                    <option value="finanzen">Finanzen</option>
                    <option value="chronik">Chronik</option>
                    <option value="kultur">Kultur</option>
                    <option value="medien">Medien</option>
                    <option value="karriere">Karriere</option>
                </select>
            </div>
            <div class="">
                <label style="display: block; width: 100%" for="post-search-input">Suchbegriff</label>
                <input type="search" x-model="search" x-on:keydown.enter="load_ots()">
                <input type="button" class="button" value="suchen" x-on:click="load_ots()">
            </div>
        </div>

        <hr>

        <div>
            <table class="wp-list-table widefat fixed table-view-list posts striped">
                <thead>
                <tr>
                    <th scope="col" id="title" class="manage-column">
                        Bilder
                    </th>
                    <th scope="col" id="author" class="manage-column">Aussendung</th>
                    <th scope="col" id="author" class="manage-column">Datum</th>
                    <th scope="col" id="author" class="manage-column">Herausgeber</th>
                    <th scope="col" id="author" class="manage-column">Importieren</th>
                </tr>
                </thead>
                <tbody>
                <template x-if="loading">
                    <tr>
                        <td colspan="5">
                            <div class="placeholder centered">
                                <div class="lds-spinner">
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </template>

                <template x-if="!ots.length && !error && !loading">
                    <tr>
                        <td colspan="5">
                            <div class="placeholder centered">
                                <p>Kein Suchergebnis, ändere die Suche</p>
                            </div>
                        </td>
                    </tr>
                </template>

                <template x-if="error">
                    <tr>
                        <td colspan="5">
                            <div class="placeholder centered error-message">
                                <svg style="width: 40px; height: 40px" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"></path>
                                </svg>
                                <p class="error-message" x-text="error"></p>
                            </div>
                        </td>
                    </tr>
                </template>

                <template x-for="( ots_meldung, index ) in ots" :key="index">
                    <tr>
                        <td>
                            <template x-if="ots_meldung.ANHANG != null">
                                <img :src="ots_meldung.ANHANG[0]['VORSCHAU']['thumb']" style="width: 150px;"/>
                            </template>
                            <template x-if="ots_meldung.ANHANG == null">
                                <div class="image-placeholder">
                                    <p>Kein Bild</p>
                                </div>
                            </template>
                        </td>
                        <td>
                            <h3 x-text="ots_meldung.TITEL"></h3>
                            <p x-text="ots_meldung.LEAD"></p>
                        </td>
                        <td x-text="ots_meldung.DATUM"></td>
                        <td x-text="ots_meldung.EMITTENT"></td>
                        <td>
                            <div class="centered">
                                <template x-if="importing == ots_meldung.SCHLUESSEL">
                                    <div class="lds-spinner">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                </template>
                                <template x-if="!importing && !imported.includes(ots_meldung.SCHLUESSEL)">
                                    <input type="button" id="search-submit" class="button" value="importieren" x-on:click="import_ots( ots_meldung.SCHLUESSEL )">
                                </template>
                                <template x-if="imported.includes(ots_meldung.SCHLUESSEL)">
                                    <p>Bereits importiert</p>
                                </template>
                            </div>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>
    </div>
</div>