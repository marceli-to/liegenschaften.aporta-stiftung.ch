import CollectionCreate from '@/views/backend/pages/collection/Create.vue';
import CollectionEdit from '@/views/backend/pages/collection/Edit.vue';
import CollectionList from '@/views/backend/pages/collection/List.vue';

const routes = [
  {
    name: 'collection-create',
    path: '/administration/kollektion',
    component: CollectionCreate,
  },
  {
    name: 'collection-edit',
    path: '/administration/kollektion/bearbeiten/:uuid',
    component: CollectionEdit,
  },
  {
    name: 'collections',
    path: '/administration/angebote/:uuid?',
    component: CollectionList,
  },
];

export default routes;