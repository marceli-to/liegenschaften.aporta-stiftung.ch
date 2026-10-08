import { createRouter, createWebHistory } from 'vue-router';
import { resetUi } from '@/store';

import ErrorForbidden from '@/views/backend/errors/Forbidden.vue';
import ErrorNotFound from '@/views/backend/errors/NotFound.vue';
import ApartmentIndex from '@/views/backend/pages/apartment/List.vue';
import ApartmentShow from '@/views/backend/pages/apartment/Show.vue';
import ApartmentUpdate from '@/views/backend/pages/apartment/Update.vue';
import CollectionCreate from '@/views/backend/pages/collection/Create.vue';
import CollectionEdit from '@/views/backend/pages/collection/Edit.vue';
import CollectionList from '@/views/backend/pages/collection/List.vue';
import TenantIndex from '@/views/backend/pages/tenant/List.vue';
import UserIndex from '@/views/backend/pages/user/List.vue';
import UserProfile from '@/views/backend/pages/user/Profile.vue';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    // Authorization
    { name: 'forbidden', path: '/forbidden', component: ErrorForbidden },
    { name: 'not-found', path: '/not-found', component: ErrorNotFound },

    // Apartments
    { name: 'apartments', path: '/administration/objekte/', component: ApartmentIndex },
    { name: 'apartment-show', path: '/administration/objekt/:uuid/anzeigen/:single?/:referrer?', component: ApartmentShow },
    { name: 'apartment-edit', path: '/administration/objekt/:uuid/bearbeiten', component: ApartmentUpdate },

    // Offers
    { name: 'collection-create', path: '/administration/kollektion', component: CollectionCreate },
    { name: 'collection-edit', path: '/administration/kollektion/bearbeiten/:uuid', component: CollectionEdit },
    { name: 'collections', path: '/administration/angebote/:uuid?', component: CollectionList },

    // Tenants
    { name: 'tenants', path: '/administration/mieter/', component: TenantIndex },

    // Users
    { name: 'users', path: '/administration/benutzer', component: UserIndex },
    { name: 'user-profile', path: '/administration/benutzer/profil', component: UserProfile },
  ],
});

router.beforeEach(() => resetUi());

export default router;
