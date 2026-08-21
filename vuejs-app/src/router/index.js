import { createRouter, createWebHistory } from 'vue-router';
// បន្ថែម Import User Store នៅទីនេះ
import { useUserStore } from '@/stores/user'; 

// Auth Components
import Signin from '@/components/auth/Signin.vue';
import Signout from '@/components/auth/Signout.vue';
import ResetPassword from '@/components/auth/ResetPassword.vue';
import SetNewPassword from '@/components/auth/SetNewPassword.vue';
import GoogleOAuth from '@/components/google-oauth/GoogleOAuth.vue';

// Page Components
import Dashboard from '@/components/pages/Dashboard.vue';
import Profile from '@/components/auth/Profile.vue';
import User from '@/components/pages/User.vue';
import Department from '@/components/pages/Department.vue';
import Division from '@/components/pages/Division.vue';
import Position from '@/components/pages/Position.vue';
import Backup from '@/components/pages/Backup.vue';

// Layout Components
import Navbar from "@/components/includes/Navbar.vue";
import LeftSidebar from "@/components/includes/LeftSidebar.vue";
import RightSidebar from "@/components/includes/RightSidebar.vue";
import Footer from "@/components/includes/Footer.vue";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'auth.signin',
      component: Signin,
      meta: { guarded: false },
    },
    {
      path: '/signout',
      name: 'auth.signout',
      component: Signout,
      meta: { guarded: true },
    },
    {
      path: '/reset-password',
      name: 'auth.reset-password',
      component: ResetPassword,
      meta: { guarded: false },
    },
    {
      path: '/set-new-password',
      name: 'auth.set-new-password',
      component: SetNewPassword,
      meta: { guarded: false },
    },
    {
      path: '/google/oauth/callback',
      name: 'auth.google.oauth.callback',
      component: GoogleOAuth,
      meta: { guarded: false },
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      components: {
        default: Dashboard,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
        footer: Footer,
      },
      meta: { guarded: true, requiresAdmin: true },
    },
    {
      path: '/profile',
      name: 'profile',
      components: {
        default: Profile,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
        footer: Footer,
      },
      meta: { guarded: true },
    },
    {
      path: '/users',
      name: 'users',
      components: {
        default: User,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
        footer: Footer,
      },
      meta: { guarded: true, requiresAdmin: true }, 
  
    },
    {
      path: '/departments',
      name: 'departments',
      components: {
        default: Department,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
        footer: Footer,
      },
      meta: { guarded: true, requiresAdmin: true }, 
    },
    {
      path: '/divisions',
      name: 'divisions',
      components: {
        default: Division,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
        footer: Footer,
      },
       meta: { guarded: true, requiresAdmin: true }, 
    },
    {
      path: '/positions',
      name: 'positions',
      components: {
        default: Position,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
        footer: Footer,
      },
      meta: { guarded: true, requiresAdmin: true }, 
    },
    {
      path: '/backups',
      name: 'backups',
      components: {
        default: Backup,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
        footer: Footer,
      },
      meta: { guarded: true, requiresAdmin: true }, 
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/',
    }
  ],
});
router.beforeEach((to, from) => {
  const userStore = useUserStore();
  const userLevel = userStore.level || userStore.user?.level || localStorage.getItem('user_level') || '';

  // ឆែកមើលថាតើទំព័រនេះត្រូវការសិទ្ធិ Admin ឬអត់?
  if (to.meta.requiresAdmin) {
    // បើ Route ត្រូវការសិទ្ធិ Admin ហើយគាត់ជា ADMIN នោះឱ្យចូល
    if (userLevel.toUpperCase() === 'ADMIN') {
      return true; // ជំនួសឱ្យ next()
    } else {
      // បើគាត់ជា USER ធម្មតា បញ្ជូនគាត់ទៅទំព័រ Profile វិញ
      return { name: 'profile' }; // ជំនួសឱ្យ next({ name: 'profile' })
    }
  } 
  
  // សម្រាប់ Route ផ្សេងទៀតដែលមិនត្រូវការ Admin
  return true; // ជំនួសឱ្យ next()
});


export default router;