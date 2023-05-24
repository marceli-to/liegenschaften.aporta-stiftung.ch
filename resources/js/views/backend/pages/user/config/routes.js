import UserIndex from '@/views/backend/pages/user/List.vue';
import UserProfile from '@/views/backend/pages/user/Profile.vue';

const routes = [
  {
    name: 'users',
    path: '/administration/benutzer',
    component: UserIndex,
  },
  {
    name: 'user-profile',
    path: '/administration/benutzer/profil',
    component: UserProfile,
  },
];

export default routes;