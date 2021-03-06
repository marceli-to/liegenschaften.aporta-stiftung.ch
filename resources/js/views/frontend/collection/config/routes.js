import CollectionList from '@/views/frontend/collection/List.vue';
import CollectionShow from '@/views/frontend/collection/Show.vue';

const routes = [
  {
    name: 'collection-list',
    path: '/angebot/:uuid/:hash?',
    component: CollectionList,
  },
  {
    name: 'collection-show',
    path: '/angebot/:uuid/detail/:itemUuid',
    component: CollectionShow,
  },
];

export default routes;