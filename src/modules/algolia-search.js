const algoliasearch = require('algoliasearch/lite');
const instantsearch = require('instantsearch.js').default;
import { hits, configure } from 'instantsearch.js/es/widgets';

;((w, $) => {
  'use strict';
  const {algolia_app} = B_HELPERS_DATA;
  const {APP_ID, API_KEY} = algolia_app;

  if(!APP_ID || !API_KEY) {
    return;
  }

  const searchClient = algoliasearch(APP_ID, API_KEY);
  const search = [];
  
  const search_instant = {
    ALGOLIA_SEARCH_RESULT_PRODUCT: {
      configure: {
        hitsPerPage: 6,
      },
      onRender: () => { },
      instantsearch: instantsearch({
        indexName: 'wp_posts_product',
        searchClient,
      })
    },
    ALGOLIA_SEARCH_RESULT_CAT: {
      configure: {
        hitsPerPage: 4,
      },
      onRender: () => { },
      instantsearch: instantsearch({
        indexName: 'wp_terms_product_cat',
        searchClient,
      })
    },
    ALGOLIA_SEARCH_RESULT_PAGE: {
      configure: {
        hitsPerPage: 3,
      },
      onRender: () => { },
      instantsearch: instantsearch({
        indexName: 'wp_posts_page',
        searchClient,
      })
    },
    ALGOLIA_SEARCH_RESULT_POST: {
      configure: {
        hitsPerPage: 3,
      },
      onRender: () => { },
      instantsearch: instantsearch({
        indexName: 'wp_posts_post',
        searchClient,
      })
    },
  }

  const searchResultActive = () => {
    $(document.body).on('Algolia:SearchResultActive', (e, active) => {
      if(active == true) {
        document.body.classList.add('__algolia-search-result-active')
      } else {
        document.body.classList.remove('__algolia-search-result-active')
      }
    })
  }

  const searchFieldHandle = () => {
    const $input = $('input.algolia-search__text-field');

    $('body').on('input', 'input.algolia-search__text-field', function(e) {
      e.preventDefault();
    })

    $input.on({
      'focus': () => {
        $(document.body).trigger('Algolia:SearchResultActive', [true])
      },
      'blur': () => {
        $(document.body).trigger('Algolia:SearchResultActive', [false])
      }
    })
  }

  const init = () => {
    searchResultActive();
    searchFieldHandle();

    $.each(search_instant, (__index, __) => {
      const _S = __.instantsearch;
      const _ID = `${ __index }`;

      const wg_hits = hits( {
        container: `#${ _ID }`,
        templates:{
          empty: 'No results for <q>{{ query }}</q>',
          item: wp.template(`${ _ID }`)
        },
      } );

      const wg_configure = configure(__.configure);

      _S.addWidget( wg_configure );
      _S.addWidget( wg_hits );
      _S.start();
      search.push(_S);

      _S.on('render', __.onRender)
    })
  }

  $(init);

})(window, jQuery)