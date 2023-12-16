const algoliasearch = require('algoliasearch/lite');
const instantsearch = require('instantsearch.js').default;
import { index, searchBox, hits, configure } from 'instantsearch.js/es/widgets';
((w, $) => {
    'use strict';
    const { algolia_app } = B_HELPERS_DATA;
    const { APP_ID, API_KEY } = algolia_app;

    if (!APP_ID || !API_KEY) {
        return;
    }

    const searchClient = algoliasearch(APP_ID, API_KEY);
    const search = instantsearch({
        indexName: 'wp_posts_product',
        searchClient,
        insights: true,
    });

    search.addWidgets([
        configure({
            hitsPerPage: 6,
        }),
        searchBox({
            container: '#searchbox',
            showReset: false,
            showSubmit: false,
            placeholder: 'Search...',
            cssClasses: {
              input: 'algolia-search__text-field',
            },
        }),
        hits({
            container: '#ALGOLIA_SEARCH_RESULT_PRODUCT',
            templates: {
                empty: 'No results for <q>{{ query }}</q>',
                item: wp.template('ALGOLIA_SEARCH_RESULT_PRODUCT')
            },
        }),
        index({
          indexName: 'wp_terms_product_cat'
        }).addWidgets([
          configure({
            hitsPerPage: 4,
          }),
          hits({
            container: '#ALGOLIA_SEARCH_RESULT_CAT',
            templates: {
              empty: 'No results for <q>{{ query }}</q>',
              item: wp.template('ALGOLIA_SEARCH_RESULT_CAT')
            },
          }),
        ]),
        index({
          indexName: 'wp_posts_page'
        }).addWidgets([
          configure({
            hitsPerPage: 3,
          }),
          hits({
            container: '#ALGOLIA_SEARCH_RESULT_PAGE',
            templates: {
              empty: 'No results for <q>{{ query }}</q>',
              item: wp.template('ALGOLIA_SEARCH_RESULT_PAGE')
            },
          }),
        ]),
        index({
          indexName: 'wp_posts_post'
        }).addWidgets([
          configure({
            hitsPerPage: 3,
          }),
          hits({
            container: '#ALGOLIA_SEARCH_RESULT_POST',
            templates: {
              empty: 'No results for <q>{{ query }}</q>',
              item: wp.template('ALGOLIA_SEARCH_RESULT_POST')
            },
          }),
        ]),
    ]);

    search.on('render',() => { })
    search.start();

    const searchResultActive = () => {
        $(document.body).on('Algolia:SearchResultActive', (e, active) => {
            if (active == true) {
                document.body.classList.add('__algolia-search-result-active')
            } else {
              //  document.body.classList.remove('__algolia-search-result-active')
            }
        });
        $(document).on('click', function(e) {
          if ($(e.target).closest(".algolia-search__result-entry").length === 0 && !$(e.target).hasClass('ais-SearchBox-input')) {
            document.body.classList.remove('__algolia-search-result-active')
          }
        });
    }

    const searchFieldHandle = () => {
        const $input = $('input.ais-SearchBox-input');

        $('body').on('input', 'input.ais-SearchBox-input', function(e) {
            e.preventDefault();
        })

        $(window).on('scroll', function(e) {
          $input.trigger('blur');
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

    const init_search = () => {
        searchResultActive();
        searchFieldHandle();
    }

    $(init_search);

})(window, jQuery)