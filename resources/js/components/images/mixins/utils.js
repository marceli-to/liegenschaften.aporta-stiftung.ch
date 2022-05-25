export default {
  methods: {
    getSource(image, template, maxWidth = 2000, maxHeight = 1250) {
      let coords = '';
      if (image.coords_w && image.coords_h) {
        coords = `/${maxWidth}/${maxHeight}/${image.coords_w},${image.coords_h},${image.coords_x},${image.coords_y}`;
      }
      return `/img/${template}/${image.name}${coords}`;
    },
  }
};
