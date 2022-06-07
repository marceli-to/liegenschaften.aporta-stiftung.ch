import ApartmentIndex from '@/views/backend/pages/apartment/List.vue';
import ApartmentShow from '@/views/backend/pages/apartment/Show.vue';
import ApartmentUpdate from '@/views/backend/pages/apartment/Update.vue';

const routes = [
  {
    name: 'apartments',
    path: '/administration/objekte/',
    component: ApartmentIndex,
  },
  
  {
    name: 'apartment-show',
    path: '/administration/objekt/:uuid/anzeigen',
    component: ApartmentShow,
  },

  {
    name: 'apartment-edit',
    path: '/administration/objekt/:uuid/bearbeiten',
    component: ApartmentUpdate,
  },
];

export default routes;