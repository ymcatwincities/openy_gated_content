<template>
  <Modal
    class="modal-device-manager"
    :style="{'display': isShowDeviceManagerModal ? 'table' : 'none'}"
    @close="toggleDeviceManagerModal"
  >
    <template #header>Choose meeting devices</template>
    <template #body>
      <div class="select">
        <label for="audioInputDevice">Audio input source: </label>
        <select
          id="audioInputDevice"
          :value="chosenAudioInputDeviceId"
          @change="setChosenAudioInputDeviceId($event.target.value)"
        >
          <option
            v-for="device in audioInputDevices"
            :key="device.deviceId"
            :value="device.deviceId"
          >{{ device.label }}
          </option>
        </select>
      </div>
      <!--div
        class="select"
        v-if="audioOutputDevices.length > 0"
      >
        <label for="audioOutputDevice">Audio output destination: </label>
        <select
          id="audioOutputDevice"
          :value="chosenAudioOutputDeviceId"
          @change="setChosenAudioOutputDeviceId($event.target.value)"
        >
          <option
            v-for="device in audioOutputDevices"
            :key="device.deviceId"
            :value="device.deviceId"
          >{{ device.label }}
          </option>
        </select>
      </div-->
      <div class="select">
        <label for="videoInputDevice">Video source: </label>
        <select
          id="videoInputDevice"
          :value="chosenVideoInputDeviceId"
          @change="setChosenVideoInputDeviceId($event.target.value)"
        >
          <option
            v-for="device in videoInputDevices"
            :key="device.deviceId"
            :value="device.deviceId"
          >{{ device.label }}
          </option>
        </select>
      </div>
    </template>
  </Modal>
</template>
<script>
import Modal from '@/components/modal/Modal.vue';
import { mapGetters, mapActions } from 'vuex';

export default {
  components: { Modal },
  created() {
    this.$store.dispatch('loadAndParseDeviceInfo');
  },
  computed: {
    ...mapGetters([
      'isShowDeviceManagerModal',
      'videoInputDevices',
      'audioOutputDevices',
      'audioInputDevices',
      'chosenVideoInputDeviceId',
      'chosenAudioInputDeviceId',
      'chosenAudioOutputDeviceId',
      'chosenVideoInputDeviceName',
      'chosenAudioOutputDeviceName',
      'chosenAudioInputDeviceName',
    ]),
  },
  methods: {
    ...mapActions([
      'toggleDeviceManagerModal',
      'setChosenAudioOutputDeviceId',
      'setChosenAudioInputDeviceId',
      'setChosenVideoInputDeviceId',
    ]),
  },
};
</script>
