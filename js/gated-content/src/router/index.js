import Vue from 'vue';
import VueRouter from 'vue-router';
import HomeWrapper from '@/views/HomeWrapper.vue';
import NotFound from '@/views/NotFound.vue';
import VideoPage from '@/views/VideoPage.vue';
import BlogPage from '@/views/BlogPage.vue';
import CategoryPage from '@/views/CategoryPage.vue';
import CategoriesListingPage from '@/views/CategoriesListingPage.vue';
import SchedulePage from '@/views/SchedulePage.vue';
import LiveStreamPage from '@/views/LiveStreamPage.vue';
import VirtualMeetingPage from '@/views/VirtualMeetingPage.vue';
import BlogsListingPage from '@/views/BlogsListingPage.vue';
import VideosListingPage from '@/views/VideosListingPage.vue';
import FavoritesPage from '@/views/FavoritesPage.vue';
import PersonalTrainingPage from '@/views/PersonalTrainingPage.vue';
import DurationsListingPage from '@/views/DurationsListingPage.vue';
import DurationPage from '@/views/DurationPage.vue';
import InstructorsListingPage from '@/views/InstructorsListingPage.vue';
import InstructorPage from '@/views/InstructorPage.vue';

Vue.use(VueRouter);

const routes = [
  {
    path: '/',
    name: 'HomeWrapper',
    component: HomeWrapper,
    meta: { requiresAuth: true, darkMenu: true },
  },
  {
    path: '/categories',
    name: 'CategoryListing',
    component: CategoriesListingPage,
    props: true,
    meta: { requiresAuth: true },
  },
  {
    path: '/category/:id',
    name: 'Category',
    component: CategoryPage,
    props: true,
    meta: { requiresAuth: true },
  },
  {
    path: '/durations',
    name: 'DurationsListingPage',
    component: DurationsListingPage,
    props: true,
    meta: { requiresAuth: true },
  },
  {
    path: '/duration/:id',
    name: 'Duration',
    component: DurationPage,
    props: true,
    meta: { requiresAuth: true },
  },
  {
    path: '/instructors',
    name: 'InstructorsListingPage',
    component: InstructorsListingPage,
    props: true,
    meta: { requiresAuth: true },
  },
  {
    path: '/instructor/:id',
    name: 'Instructor',
    component: InstructorPage,
    props: true,
    meta: { requiresAuth: true },
  },
  {
    path: '/videos',
    name: 'VideoListing',
    component: VideosListingPage,
    meta: { requiresAuth: true, darkMenu: true },
  },
  {
    path: '/video/:id',
    name: 'Video',
    component: VideoPage,
    props: true,
    meta: { requiresAuth: true, darkMenu: true },
  },
  {
    path: '/live-stream',
    redirect: { name: 'Schedule' },
    name: 'LiveStreamListing',
  },
  {
    path: '/live-stream/:id',
    name: 'LiveStream',
    component: LiveStreamPage,
    props: true,
    meta: { requiresAuth: true },
  },
  {
    path: '/virtual-meeting',
    name: 'VirtualMeetingListing',
    redirect: { name: 'Schedule' },
  },
  {
    path: '/virtual-meeting/:id',
    name: 'VirtualMeeting',
    component: VirtualMeetingPage,
    props: true,
    meta: { requiresAuth: true },
  },
  {
    path: '/blogs',
    name: 'BlogsListing',
    component: BlogsListingPage,
    meta: { requiresAuth: true, darkMenu: true },
  },
  {
    path: '/blog-post/:id',
    name: 'BlogPage',
    component: BlogPage,
    props: true,
    meta: { requiresAuth: true },
  },
  {
    path: '/favorites',
    name: 'Favorites',
    component: FavoritesPage,
    props: true,
    meta: { requiresAuth: true },
  },
  {
    path: '/schedule',
    name: 'Schedule',
    component: SchedulePage,
    props: true,
    meta: { requiresAuth: true },
  },
  {
    path: '/personal-training/:id',
    name: 'PersonalTraining',
    component: PersonalTrainingPage,
    props: true,
    meta: { requiresAuth: true },
  },
  {
    path: '/blog-post',
    redirect: { name: 'BlogsListing' },
  },
  {
    path: '/categories/blog',
    redirect: { name: 'BlogsListing' },
  },
  {
    path: '/categories/video',
    redirect: { name: 'VideoListing' },
  },
  {
    path: '/category/blog/:cid',
    redirect: '/category/:cid',
  },
  {
    path: '/category/video/:cid',
    redirect: '/category/:cid',
  },
  {
    path: '*',
    component: NotFound,
  },
];

const router = new VueRouter({
  routes,
  // Scroll to top on each route.
  scrollBehavior() {
    return { x: 0, y: 0 };
  },
});

/**
 * Add current route name to body class as "vy-route-{name}".
 */
router.beforeEach((to, from, next) => {
  document.body.classList.forEach((value) => {
    if (value.indexOf('vy-route-') !== -1) {
      document.body.classList.remove(value);
    }
  });

  document.body.classList.add(`vy-route-${to.name}`);

  next();
});

export default router;
