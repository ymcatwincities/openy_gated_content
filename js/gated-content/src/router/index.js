import Vue from 'vue';
import VueRouter from 'vue-router';
import Home from '@/views/Home.vue';
import NotFound from '@/views/NotFound.vue';
import VideoPage from '@/views/VideoPage.vue';
import BlogPage from '@/views/BlogPage.vue';
import CategoryPage from '@/views/CategoryPage.vue';
import CategoriesListingPage from '@/views/CategoriesListingPage.vue';
import SchedulePage from '@/views/SchedulePage.vue';
import LiveStreamPage from '@/views/LiveStreamPage.vue';
import LiveStreamListingPage from '@/views/LiveStreamListingPage.vue';
import VirtualMeetingPage from '@/views/VirtualMeetingPage.vue';
import VirtualMeetingListingPage from '@/views/VirtualMeetingListingPage.vue';
import BlogsListingPage from '@/views/BlogsListingPage.vue';
import VideosListingPage from '@/views/VideosListingPage.vue';
import FavoritesPage from '@/views/FavoritesPage.vue';

Vue.use(VueRouter);

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
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
    path: '/category/:cid',
    name: 'Category',
    component: CategoryPage,
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
    name: 'LiveStreamListing',
    component: LiveStreamListingPage,
    props: true,
    meta: { requiresAuth: true },
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
    component: VirtualMeetingListingPage,
    props: true,
    meta: { requiresAuth: true },
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

export default router;
