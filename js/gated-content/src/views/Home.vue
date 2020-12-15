<template>
  <div class="home">
    <ParagraphHeadline
      v-if="isHeadlineEnabled"
    ></ParagraphHeadline>

    <PageHeader title="Dashboard"></PageHeader>

    <VideoListing
      :featured="true"
      :viewAll="true"
      :limit="8"
      :title="config.components.gc_video.title"
      v-if="isActive('gc_video')"
    />
    <EventListing
      :featured="true"
      :viewAll="true"
      :limit="8"
      :msg="'Live streams not found.'"
      :title="config.components.live_stream.title"
      v-if="isActive('live_stream')"
    />
    <EventListing
      :title="config.components.virtual_meeting.title"
      :featured="true"
      :viewAll="true"
      :limit="8"
      :eventType="'virtual_meeting'"
      :msg="'Virtual Meetings not found.'"
      v-if="isActive('virtual_meeting')"
    />
    <BlogListing
      :featured="false"
      :viewAll="true"
      :limit="8"
      :title="config.components.vy_blog_post.title"
      v-if="isActive('vy_blog_post')"
    />
  </div>
</template>

<script>
import BlogListing from '@/components/blog/BlogListing.vue';
import VideoListing from '@/components/video/VideoListing.vue';
import EventListing from '@/components/event/EventListing.vue';
import ParagraphHeadline from '@/components/ParagraphHeadline.vue';
import { SettingsMixin } from '@/mixins/SettingsMixin';
import { mapGetters } from 'vuex';
import PageHeader from '@/components/PageHeader.vue';

export default {
  name: 'Home',
  mixins: [SettingsMixin],
  components: {
    PageHeader,
    BlogListing,
    VideoListing,
    EventListing,
    ParagraphHeadline,
  },
  computed: {
    ...mapGetters([
      'isHeadlineEnabled',
    ]),
  },
  methods: {
    isActive(component) {
      return this.config.components[component].status;
    },
  },
};
</script>
