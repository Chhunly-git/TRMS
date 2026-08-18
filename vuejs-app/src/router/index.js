import Profile from '@/components/auth/Profile.vue';
import ResetPassword from '@/components/auth/ResetPassword.vue';
import SetNewPassword from '@/components/auth/SetNewPassword.vue';
import Signin from '@/components/auth/Signin.vue';
import Signout from '@/components/auth/Signout.vue';
import Signup from '@/components/auth/Signup.vue';
import VerifyEmail from '@/components/auth/VerifyEmail.vue';
import GoogleOAuth from '@/components/google-oauth/GoogleOAuth.vue';
import Dashboard from '@/components/pages/Dashboard.vue';
import User from '@/components/pages/User.vue';
import Backup from '@/components/pages/Backup.vue';
import Employee from '@/components/pages/Employee.vue';
import Department from '@/components/pages/Department.vue';
import Division from '@/components/pages/Division.vue';
import Position from '@/components/pages/Position.vue';// 👈 Import Officers Index
import { createRouter, createWebHistory } from 'vue-router';

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
    },
    {
      path: '/signup',
      name: 'auth.signup',
      component: Signup,
      meta: { guarded: false },
    },
    {
      path: '/verify/email',
      name: 'auth.verify.email',
      component: VerifyEmail,
      meta: { guarded: false },
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
      meta: { guarded: true },
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
      meta: { guarded: true },
    },
    {
      path: '/employees',
      name: 'employees',
      components: {
        default: Employee,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
        footer: Footer,
      },
      meta: { guarded: true },
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
      meta: { guarded: true },
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
      meta: { guarded: true },
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
      meta: { guarded: true },
    },
    // Route សម្រាប់គ្រប់គ្រងមន្ត្រី (Officers)
    
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
      meta: { guarded: true },
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/dashboard',
    }
  ],
});

export default router;