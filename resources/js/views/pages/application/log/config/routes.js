import ApplicationLogIndex from '@/views/pages/application/log/List.vue';

const routes = [
  {
    name: 'application-logs',
    path: '/gesuche/:type/gesuch/:uuid/protokoll',
    component: ApplicationLogIndex,
  },
];

export default routes;