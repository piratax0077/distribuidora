/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import DataTable from 'datatables.net';
import 'datatables.net-dt/css/jquery.dataTables.min.css';

//const Swal = require('sweetalert2');
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

import Vuex from 'vuex';

window.Vue = require('vue');

Vue.use(Vuex);


Vue.use(VueSweetalert2);


import storeData from "./store/index";

const store = new Vuex.Store(
    storeData
);

new Vue({
    el: '#app',
    store,
    mounted() {
        $(function() {
            $('#miTabla').DataTable();
            $('#tabla_giros').DataTable();
        });
    }
});