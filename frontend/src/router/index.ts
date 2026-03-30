import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      component: () => import('@/layouts/AuthLayout.vue'),
      meta: { public: true },
      children: [
        {
          path: '',
          name: 'Login',
          component: () => import('@/modules/Auth/LoginPage.vue'),
        },
      ],
    },
    {
      path: '/',
      component: () => import('@/layouts/AdminLayout.vue'),
      children: [
        { path: '', redirect: '/dashboard' },
        {
          path: 'dashboard',
          name: 'Dashboard',
          meta: { title: 'Dashboard' },
          component: () => import('@/modules/Dashboard/DashboardPage.vue'),
        },
        {
          path: 'customers',
          name: 'Customers',
          meta: { title: 'Kontrahenci' },
          component: () => import('@/modules/Customers/CustomersPage.vue'),
        },
        {
          path: 'access',
          name: 'Access',
          meta: { title: 'Uprawnienia' },
          component: () => import('@/modules/Access/AccessPage.vue'),
        },
        {
          path: 'admin',
          name: 'Admin',
          meta: { title: 'Użytkownicy' },
          component: () => import('@/modules/Admin/AdminPage.vue'),
        },
      ],
    },
    { path: '/:pathMatch(.*)*', redirect: '/dashboard' },
  ],
})

router.beforeEach((to, _from, next) => {
  const token = localStorage.getItem('venom_token')
  const isPublic = to.meta.public

  if (isPublic && token) return next('/dashboard')
  if (!isPublic && !token) return next('/login')
  next()
})

export default router
