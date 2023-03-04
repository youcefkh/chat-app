/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import "bootstrap/dist/css/bootstrap.min.css";
import "./bootstrap";
//import "./emitter/eventBus"
import { createApp } from 'vue';
import axios from 'axios'
import router from './router';
import index from './Index.vue';
import store from './store';

// Vuetify
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { aliases, mdi } from 'vuetify/iconsets/mdi'
import '@mdi/font/css/materialdesignicons.css'
import '@fortawesome/fontawesome-free/css/all.css'

const vuetify = createVuetify({
  icons: {
    defaultSet: 'mdi',
    aliases,
    sets: {
      mdi,
    }
  },
  components,
  directives,
})

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
*/

//global css
import "../css/app.css"

const app = createApp({
  components: {index},
  beforeCreate() { 
    store.dispatch('setStoredState');
    store.dispatch('initializeEcho');
  }
});
app.use(router);
app.use(store);
app.use(vuetify);
app.config.globalProperties.axios=axios;
app.mount('#app');
