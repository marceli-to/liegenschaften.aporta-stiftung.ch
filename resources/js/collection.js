import { createApp } from 'vue';
import NProgress from 'nprogress';
import { createRouter, createWebHistory } from 'vue-router';
import Collection from '@/Collection.vue';
import CollectionList from '@/views/frontend/collection/List.vue';
import CollectionShow from '@/views/frontend/collection/Show.vue';

// Spinner only
NProgress.configure({ showBar: false });

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { name: 'collection-list', path: '/angebot/:uuid/:hash?', component: CollectionList },
    { name: 'collection-show', path: '/angebot/:uuid/detail/:itemUuid', component: CollectionShow },
  ],
});

// The estate comes from the page (data-estate), not from the API
const el = document.getElementById('collection');
if (el) {
  createApp(Collection, { estate: el.dataset.estate, estateKey: el.dataset.estateKey })
    .use(router)
    .mount(el);
}
