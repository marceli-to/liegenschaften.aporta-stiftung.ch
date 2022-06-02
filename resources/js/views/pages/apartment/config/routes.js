import ApartmentIndex from '@/views/pages/apartment/List.vue';
import ApartmentShow from '@/views/pages/apartment/Show.vue';
import ApartmentUpdate from '@/views/pages/apartment/Update.vue';

const routes = [
  {
    name: 'apartments',
    path: '/administration/objekte/',
    component: ApartmentIndex,
  },
  
  {
    name: 'apartment-show',
    path: '/administration/objekt/:id/anzeigen',
    component: ApartmentShow,
  },

  {
    name: 'apartment-edit',
    path: '/administration/objekt/:id/bearbeiten',
    component: ApartmentUpdate,
  },
];

export default routes;