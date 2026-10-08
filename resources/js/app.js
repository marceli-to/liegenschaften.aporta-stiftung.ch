import { createApp } from 'vue';
import NProgress from 'nprogress';
import router from '@/router';
import { handleErrors } from '@/lib/http';
import App from '@/App.vue';

// Spinner only
NProgress.configure({ showBar: false });

handleErrors(router);

// Key of the estate the admin works on (the isometry needs it)
const el = document.getElementById('app');
createApp(App)
  .provide('estateKey', el.dataset.estate)
  .use(router)
  .mount(el);
