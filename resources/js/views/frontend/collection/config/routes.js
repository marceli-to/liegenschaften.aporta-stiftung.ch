import CollectionList from '@/views/frontend/collection/List.vue';
import CollectionShow from '@/views/frontend/collection/Show.vue';

const routes = [
  {
    name: 'collection-list',
    path: '/angebot/:uuid',
    component: CollectionList,
  },
  {
    name: 'collection-show',
    path: '/angebot/:uuid/detail/:collectionUuid',
    component: CollectionShow,
  },
];

export default routes;