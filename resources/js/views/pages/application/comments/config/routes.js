import ApplicationCommentIndex from '@/views/pages/application/comments/List.vue';

const routes = [
  {
    name: 'application-comments',
    path: '/gesuche/:type/gesuch/:uuid/kommentare',
    component: ApplicationCommentIndex,
  },
];

export default routes;