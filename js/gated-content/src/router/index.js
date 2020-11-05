import Vue from 'vue';
import VueRouter from 'vue-router';
import Home from '@/views/Home.vue';
import NotFound from '@/views/NotFound.vue';
import VideoPage from '@/views/VideoPage.vue';
import BlogPage from '@/views/BlogPage.vue';
import CategoryPage from '@/views/CategoryPage.vue';
import CategoriesListing from '@/views/CategoriesListing.vue';
import LiveStreamPage from '@/views/LiveStreamPage.vue';
import LiveStreamListingPage from '@/views/LiveStreamListingPage.vue';
import VirtualMeetingPage from '@/views/VirtualMeetingPage.vue';
import VirtualMeetingListingPage from '@/views/VirtualMeetingListingPage.vue';

Vue.use(VueRouter);

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
    meta: { requiresAuth: true },
  },
  {
    path: '/categories/:type',
    name: 'CategoryListing',
    component: CategoriesListing,
    props: true,
    meta: { requiresAuth: true },
  },
  {
    path: '/category/:type/:cid',
    name: 'Category',
    component: CategoryPage,
    props: true,
    meta: { requiresAuth: true },
  },
  {
    path: '/video/:id',
    name: 'Video',
    component: VideoPage,
    props: true,
    meta: { requiresAuth: true },
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
    path: '/blog-post/:id',
    name: 'BlogPage',
    component: BlogPage,
    props: true,
    meta: { requiresAuth: true },
  },
  {
    path: '/categories',
    redirect: '/categories/video',
  },
  {
    path: '/blog-post',
    redirect: '/categories/blog',
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
