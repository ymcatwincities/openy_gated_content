<template>
  <span v-html="require(`!html-loader!../assets/svg/${icon}.svg`)" class="svg-container"></span>
</template>

<script>
function recursivelyRemoveFill(el) {
  if (!el) {
    return;
  }
  el.removeAttribute('fill');
  [].forEach.call(el.children, (child) => {
    recursivelyRemoveFill(child);
  });
}

export default {
  name: 'SvgIcon',
  props: {
    icon: {
      type: String,
      default: null,
    },
    cssFill: {
      type: Boolean,
      default: true,
    },
    growByHeight: {
      type: Boolean,
      default: true,
    },
  },
  mounted() {
    this.initSvgFile();
  },
  updated() {
    this.initSvgFile();
  },
  methods: {
    initSvgFile() {
      if (this.$el.firstElementChild.nodeName === 'svg') {
        const svgElement = this.$el.firstElementChild;
        // use `viewBox` attribute to get the svg's inherent width and height
        const viewBox = svgElement.getAttribute('viewBox').split(' ').map((n) => Number(n));
        const widthToHeight = (viewBox[2] / viewBox[3]).toFixed(2);
        if (this.cssFill) {
          // recursively remove all fill attribute of element and its nested children
          recursivelyRemoveFill(svgElement);
        }
        // set width and height relative to font size
        // if growByHeight is true, height set to 1em else width set to 1em
        // and remaining is calculated based on widthToHeight ratio
        if (this.growByHeight) {
          svgElement.setAttribute('height', '1em');
          svgElement.setAttribute('width', `${widthToHeight}em`);
        } else {
          svgElement.setAttribute('width', '1em');
          svgElement.setAttribute('height', `${1 / widthToHeight}em`);
        }
        svgElement.classList.add('svg-class');
      }
    },
  },
};
</script>
