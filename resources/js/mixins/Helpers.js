export default {

  methods: {

    moneyFormat(amount) {
      return amount.toFixed(2);
    },

    shorten(str, length = 10, suffix = "...") {
      return str.substring(0, length) + suffix;
    },

    randomString() {
      return Math.random().toString(36).slice(2);
    },

    validateRequired(str) {
      return str != null && str.length > 0 ? true : false;
    },

    validateEmail(email) {
      let filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
      return email != null && email.length > 0 && filter.test(email) ? true : false;
    }
  }
};