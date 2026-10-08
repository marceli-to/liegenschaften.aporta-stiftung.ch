import { createApp } from 'vue';
import NProgress from 'nprogress';
import router from '@/router';
import { handleErrors } from '@/lib/http';
import App from '@/App.vue';

// Spinner only
NProgress.configure({ showBar: false });

handleErrors(router);

createApp(App)
  .use(router)
  .mount('#app');
