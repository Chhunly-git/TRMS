import { createRouter, createWebHistory } from "vue-router";
// បន្ថែម Import User Store នៅទីនេះ
import { useUserStore } from "@/stores/user";

// Auth Components
import Signin from "@/components/auth/Signin.vue";
import Signout from "@/components/auth/Signout.vue";
import ResetPassword from "@/components/auth/ResetPassword.vue";
import SetNewPassword from "@/components/auth/SetNewPassword.vue";
import GoogleOAuth from "@/components/google-oauth/GoogleOAuth.vue";

// Page Components
import Dashboard from "@/components/pages/Dashboard.vue";
import Setting from "@/components/auth/Setting.vue";
import User from "@/components/pages/User.vue";
import Department from "@/components/pages/Department.vue";
import Division from "@/components/pages/Division.vue";
import Position from "@/components/pages/Position.vue";
import Backup from "@/components/pages/Backup.vue";
import Attendance from "@/components/pages/Attendance.vue";
import UserAttendance from "@/components/pages/UserAttendance.vue";
import UserProfilePrint from "@/components/pages/UserProfilePrint.vue";
import Profile from "@/components/pages/Profile.vue";
import DocumentTemplateManager from "@/components/pages/DocumentTemplateManager.vue";
import DocumentTemplateList from "@/components/pages/DocumentTemplateList.vue";
import WorkSchedule from "@/components/pages/WorkSchedule.vue";


// Layout Components
import Navbar from "@/components/includes/Navbar.vue";
import LeftSidebar from "@/components/includes/LeftSidebar.vue";
import RightSidebar from "@/components/includes/RightSidebar.vue";


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      name: "auth.signin",
      component: Signin,
      meta: { guarded: false },
    },
    {
      path: "/signout",
      name: "auth.signout",
      component: Signout,
      meta: { guarded: true },
    },
    {
      path: "/reset-password",
      name: "auth.reset-password",
      component: ResetPassword,
      meta: { guarded: false },
    },
    {
      path: "/set-new-password",
      name: "auth.set-new-password",
      component: SetNewPassword,
      meta: { guarded: false },
    },
    {
      path: "/google/oauth/callback",
      name: "auth.google.oauth.callback",
      component: GoogleOAuth,
      meta: { guarded: false },
    },
    {
      path: "/dashboard",
      name: "dashboard",
      components: {
        default: Dashboard,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
       
      },
      meta: { guarded: true, permission: "dashboard" },
    },
    {
      path: "/setting",
      name: "setting",
      components: {
        default: Setting,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
        
      },
      meta: { guarded: true },
    },
    {
      path: "/my-attendances",
      name: "my-attendances",
      components: {
        default: UserAttendance,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
      },
      meta: { guarded: true, permission: "my-attendances" },
    },
    {
      path: "/profile",
      name: "profile",
      components: {
        default: Profile,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
      },
      meta: { guarded: true, permission: "profile" },
    },
    // Route សម្រាប់ User ព្រីន Profile ខ្លួនឯង
    {
      path: "/my-profile",
      name: "my-profile",
      components: {
        default: UserProfilePrint,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
      },
      meta: { guarded: true, permission: "profile" },
    },

    // Route សម្រាប់ Admin ព្រីន Profile មន្ត្រីតាម ID
   
    {
      path: "/users",
      name: "users",
      components: {
        default: User,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
       
      },
      meta: { guarded: true, permission: "users" },
    },
    {
      path: "/user-detail/:id",
      name: "user.detail",
      components: {
        default: UserProfilePrint,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
        
      },
      meta: { guarded: true, permission: "users" },
    },
    {
      path: "/departments",
      name: "departments",
      components: {
        default: Department,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
        
      },
      meta: { guarded: true, permission: "departments" },
    },
    {
      path: "/divisions",
      name: "divisions",
      components: {
        default: Division,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
       
      },
      meta: { guarded: true, permission: "divisions" },
    },
    {
      path: "/positions",
      name: "positions",
      components: {
        default: Position,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
        
      },
      meta: { guarded: true, permission: "positions" },
    },
    {
      path: "/backups",
      name: "backups",
      components: {
        default: Backup,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
        
      },
      meta: { guarded: true, permission: "backups" },
    },
    {
      path: "/attendances",
      name: "attendances",
      components: {
        default: Attendance,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
        
      },
      meta: { guarded: true, permission: "attendances" },
    },
    {
      path: "/document-templates",
      name: "document-templates",
      components: {
        default: DocumentTemplateList,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
      },
      meta: { guarded: true, permission: "document-templates" },
    },
    {
      path: "/manage/document-templates",
      name: "manage-document-templates",
      components: {
        default: DocumentTemplateManager,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
      },
      meta: { guarded: true, permission: "manage-document-templates" },
    },
    {
      path: "/work-schedules",
      name: "work-schedules",
      components: {
        default: WorkSchedule,
        navbar: Navbar,
        left_sidebar: LeftSidebar,
        right_sidebar: RightSidebar,
      },
      meta: { guarded: true, permission: "work-schedules" },
    },
    {
      path: "/:pathMatch(.*)*",
      redirect: "/",
    },
  ],
});
router.beforeEach((to, from) => {
  const userStore = useUserStore();
  const userLevel =
    userStore.level ||
    userStore.user?.level ||
    localStorage.getItem("user_level") ||
    "";

  // 1. ពិនិត្យការចូលប្រើប្រាស់ (Guarded routes)
  if (to.meta.guarded) {
    const token = userStore.getSanctumToken();
    if (!token && to.name !== "auth.signin") {
      return { name: "auth.signin" };
    }
  }

  // 2. ប្រសិនបើជា ADMIN មានសិទ្ធិចូលគ្រប់ route ទាំងអស់
  if (userLevel && userLevel.toUpperCase() === "ADMIN") {
    return true;
  }

  // 3. ឆែកមើលថាតើ route នេះត្រូវការសិទ្ធិ (permission) ដែរឬទេ?
  if (to.meta.permission) {
    if (userStore.can(to.meta.permission)) {
      return true;
    } else {
      // បើគ្មានសិទ្ធិ បញ្ជូនទៅកាន់ទំព័រ Profile វិញ
      return { name: "profile" };
    }
  }

  // 4. ពិនិត្យ fallback សម្រាប់ requiresAdmin
  if (to.meta.requiresAdmin) {
    return { name: "profile" };
  }

  // សម្រាប់ Route ផ្សេងទៀតដែលមិនមានការកំណត់
  return true;
});

export default router;
