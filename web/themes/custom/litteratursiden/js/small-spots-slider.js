var slider = tns({
  container: '.small-spots-slider',
  items: 3,
  gutter: 30,
  speed: 500,
  slideBy: 'page',
  loop: false,
  rewind: true,
  responsive: {
      241: {
          items: 1,
          edgePadding: 20
      },
      361: {
          items: 1,
          edgePadding: 80
      },
      601: {
          items: 3,
          edgePadding: 0
      },
      1025: {
          items: 3
      }
  }
});
