const algoliasearch = require('algoliasearch/lite');
const instantsearch = require('instantsearch.js').default;
import { searchBox, hits, configure } from 'instantsearch.js/es/widgets';
import { connectSearchBox } from 'instantsearch.js/es/connectors';;
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
        }),
        hits({
            container: '#ALGOLIA_SEARCH_RESULT_PRODUCT',
            templates: {
                empty: 'No results for <q>{{ query }}</q>',
                item: wp.template('ALGOLIA_SEARCH_RESULT_PRODUCT')
            },
        })
    ]);
    const search_instant = {
        ALGOLIA_SEARCH_RESULT_CAT: {
            configure: {
                hitsPerPage: 4,
            },
            instantsearch: {
                indexName: 'wp_terms_product_cat'
            }
        },
        ALGOLIA_SEARCH_RESULT_PAGE: {
            configure: {
                hitsPerPage: 3,
            },
            instantsearch: {
                indexName: 'wp_posts_page'
            }
        },
        ALGOLIA_SEARCH_RESULT_POST: {
            configure: {
                hitsPerPage: 3,
            },
            instantsearch: {
                indexName: 'wp_posts_post'
            }
        },
    }

    const searchResultActive = () => {
        $(document.body).on('Algolia:SearchResultActive', (e, active) => {
            if (active == true) {
                document.body.classList.add('__algolia-search-result-active')
            } else {
                document.body.classList.remove('__algolia-search-result-active')
            }
        })
    }

    const searchFieldHandle = () => {
        const $input = $('input.ais-SearchBox-input');

        $('body').on('input', 'input.ais-SearchBox-input', function(e) {
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

    const init_search = () => {
        searchResultActive();
        searchFieldHandle();
        search.addWidgets([
            $.each(search_instant, (__index, __) => {
                const _S = __.instantsearch;
                const _ID = `${ __index }`;

                const wg_hits = hits({
                    container: `#${ _ID }`,
                    templates: {
                        empty: 'No results for <q>{{ query }}</q>',
                        item: wp.template(`${ _ID }`)
                    },
                });
                const wg_configure = configure(__.configure);
                search.addWidget(wg_configure);
                search.addWidget(wg_hits);
            })
        ]);
    }

    search.start();

    $(init_search);

})(window, jQuery)