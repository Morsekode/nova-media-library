export default {
  methods: {
    isImage(url) {
      let ext = url.split('.').reverse()[0].toString().toLowerCase();
      let arr = [ 'png', 'svg', 'jpg', 'jpeg', 'gif', 'bmp' ];
      return arr.indexOf(ext) > -1;
    },
    isVideo(url){
      let ext = url.split('.').reverse()[0].toString().toLowerCase();
      let arr = [ 'mp4', 'mov', 'webm', 'ogg',];
      return arr.indexOf(ext) > -1;
    },
    setBg(url) {
      return this.isImage(url) ? { backgroundImage: `url(${url})`, backgroundSize: 'cover' } : {};
    },
    getPoster(url){
      let filename = url.substring(0, url.lastIndexOf('.')) || url
       return `${filename}-1.jpg`;
    }
  },
  beforeDestroy() {
    Nova.$off('nml-select-file');
    Nova.$off('nml-select-files');
  }
}
