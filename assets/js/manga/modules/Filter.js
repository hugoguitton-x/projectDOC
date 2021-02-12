import axios from "axios";


/**
 * @property {HTMLElement} content
 * @property {HTMLElement} pagination
 * @property {HTMLFormElement} form
 */
export default class Filter {

  /**
   *
   * @param {HTMLElement|null} element
   */
  constructor(element) {
    if (element === null) {
      return;
    }

    this.content = element.querySelector('.js-filter-content');
    this.pagination = element.querySelector('.js-filter-pagination');
    this.form = element.querySelector('.js-filter-form');

    this.bindEvents()
  }

  /**
   * Ajoute les comportements aux différents éléments
   */
  bindEvents() {

    this.pagination.addEventListener('click', e => {
      if (e.target.tagName === 'A') {
        e.preventDefault();
        this.loadUrl(e.target.getAttribute('href'));
      }
    })

    this.form.querySelectorAll('input').forEach(input => {
      input.addEventListener('input', () => {
        clearTimeout(this.timeout);
        this.timeout = setTimeout(this.loadForm.bind(this), 1000);
      });
    })


  }

  loadForm() {
    // @TODO display loading
    const data = new FormData(this.form);
    const url = new URL(this.form.getAttribute('action') || window.location.href);
    const params = new URLSearchParams();
    data.forEach((value, key) => {
      params.append(key, value);
    });

    this.loadUrl(url.pathname + '?' + params.toString());
  }

  loadUrl(url) {

    var _ = this;

    axios.get(`${url}&ajax=1`).then(function (response) {
      if (_.currentContent !== response.data.content) {
        _.currentContent = response.data.content;

        _.content.innerHTML = response.data.content;
        console.log('TEST')
        _.content.querySelectorAll('a.twitter-action').forEach(function (link) {
          link.addEventListener('click', _.onClickLinkTwitterAction);

          link.addEventListener('click', _.onClickLinkFollowAction);
        });
      }

      _.pagination.innerHTML = response.data.pagination;

      history.replaceState({}, '', url);


    }).catch(function (error) {
      console.error(error);
    }).finally(() => {
      // @TODO STOP LOADING
    })
  }

  onClickLinkTwitterAction(event) {
    event.preventDefault();
    const url = this.href;
    const link = this;

    axios.post(url).then(function (response) {

      link.textContent = response.data.value;

      if (link.classList.contains('twitter-enabled')) {
        link.classList.replace('twitter-enabled', 'twitter-disabled');
      } else {
        link.classList.replace('twitter-disabled', 'twitter-enabled');
      }
    }).catch(function (error) {

    })
  }

  onClickLinkFollowAction(event) {
    event.preventDefault();
    const url = this.href;
    const link = this;

    axios.post(url).then(function (response) {

      link.textContent = response.data.value;

      if (link.classList.contains('followed')) {
        link.classList.replace('followed', 'unfollowed');
      } else {
        link.classList.replace('unfollowed', 'followed');
      }
    }).catch(function (error) {

    })
  }

}
