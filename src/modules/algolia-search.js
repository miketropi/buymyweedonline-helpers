const algoliasearch = require('algoliasearch/lite');
const instantsearch = require('instantsearch.js').default;
import { hits, configure } from 'instantsearch.js/es/widgets';

;((w, $) => {
  'use strict';
  const {algolia_app} = B_HELPERS_DATA;
  const {APP_ID, API_KEY} = algolia_app;
  const searchClient = algoliasearch(APP_ID, API_KEY);
  const search = [];
  
  const search_instant = {
    ALGOLIA_SEARCH_RESULT_PRODUCT: {
      configure: {
        hitsPerPage: 6,
      },
      onRender: () => {

      },
      instantsearch: instantsearch({
        indexName: 'wp_posts_product',
        searchClient,
      })
    }
  }

  const init = () => {
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