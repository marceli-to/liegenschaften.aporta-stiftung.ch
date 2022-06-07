import CollectionForm from '@/views/backend/pages/collection/Form.vue';
import CollectionList from '@/views/backend/pages/collection/List.vue';

const routes = [
  {
    name: 'collection',
    path: '/administration/kollektion/',
    component: CollectionForm,
  },
  {
    name: 'collections',
    path: '/administration/angebote/',
    component: CollectionList,
  },
];

export default routes;