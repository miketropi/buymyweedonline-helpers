const algoliasearch = require('algoliasearch/lite');
import { autocomplete } from '@algolia/autocomplete-js';
import historyRouter from 'instantsearch.js/es/lib/routers/history';
import { connectSearchBox } from 'instantsearch.js/es/connectors';
import { createLocalStorageRecentSearchesPlugin } from '@algolia/autocomplete-plugin-recent-searches';
import { createQuerySuggestionsPlugin } from '@algolia/autocomplete-plugin-query-suggestions';
import '@algolia/autocomplete-theme-classic';

const instantsearch = require('instantsearch.js').default;
import { index, searchBox, hits, configure } from 'instantsearch.js/es/widgets';
((w, $) => {
    'use strict';
    const { algolia_app } = B_HELPERS_DATA;
    const { APP_ID, API_KEY } = algolia_app;

    if (!APP_ID || !API_KEY) {
        return;
    }
    const INSTANT_SEARCH_INDEX_NAME = 'wp_posts_product';
    const width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
    const is_mobile = width <= 980 ? true : false;
    // Mount a virtual search box to manipulate InstantSearch's `query` UI
    // state parameter.
    const virtualSearchBox = connectSearchBox(() => {});
    const instantSearchRouter = historyRouter();
    const searchClient = algoliasearch(APP_ID, API_KEY);
    // const algoliaClient = algoliasearch(APP_ID, API_KEY);

    // const searchClient = {
    //   ...algoliaClient,
    //   search(requests) {
    //     if (requests.every(({ params }) => !params.query)) {
    //       return;
    //     }
    
    //     return algoliaClient.search(requests);
    //   },
    // };
    const search = instantsearch({
        indexName: 'wp_posts_product',
        searchClient,
        insights: true,
        routing: instantSearchRouter,
    });

    search.addWidgets([
        configure({
            hitsPerPage: 6,
        }),
        virtualSearchBox({}),
        // searchBox({
        //     container: '#searchbox',
        //     showReset: false,
        //     showSubmit: false,
        //     placeholder: 'Search...',
        //     cssClasses: {
        //       input: 'algolia-search__text-field',
        //     },
        // }),

        hits({
            container: is_mobile ? '#ALGOLIA_SEARCH_RESULT_PRODUCT_MB' : '#ALGOLIA_SEARCH_RESULT_PRODUCT',
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
            container: is_mobile ? '#ALGOLIA_SEARCH_RESULT_CAT_MB' : '#ALGOLIA_SEARCH_RESULT_CAT',
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
            container: is_mobile ? '#ALGOLIA_SEARCH_RESULT_PAGE_MB' : '#ALGOLIA_SEARCH_RESULT_PAGE',
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
            container: is_mobile ? '#ALGOLIA_SEARCH_RESULT_POST_MB' : '#ALGOLIA_SEARCH_RESULT_POST',
            templates: {
              empty: 'No results for <q>{{ query }}</q>',
              item: wp.template('ALGOLIA_SEARCH_RESULT_POST')
            },
          }),
        ]),
    ]);

    search.on('render',() => { })
    search.start();
    // Build URLs that InstantSearch understands.
    function getInstantSearchUrl(indexUiState) {
      return search.createURL({ [INSTANT_SEARCH_INDEX_NAME]: indexUiState });
    }

    // Detect when an event is modified with a special key to let the browser
    // trigger its default behavior.
    function isModifierEvent(event) {
      const isMiddleClick = event.button === 1;

      return (
        isMiddleClick ||
        event.altKey ||
        event.ctrlKey ||
        event.metaKey ||
        event.shiftKey
      );
    }

    function onSelect({ setIsOpen, setQuery, event, query }) {
      // You want to trigger the default browser behavior if the event is modified.
      if (isModifierEvent(event)) {
        return;
      }
      setQuery(query);
      setIsOpen(false);
      setInstantSearchUiState({ query });
    }

    function getItemUrl({ query }) {
      return getInstantSearchUrl({ query });
    }

    function createItemWrapperTemplate({ children, query, html }) {
      const uiState = { query };

      return html`<a
        class="aa-ItemLink"
        href="${getInstantSearchUrl(uiState)}"
        onClick="${(event) => {
          if (!isModifierEvent(event)) {
            // Bypass the original link behavior if there's no event modifier
            // to set the InstantSearch UI state without reloading the page.
            event.preventDefault();
          }
        }}"
      >
        ${children}
      </a>`;
    }

    const recentSearchesPlugin = createLocalStorageRecentSearchesPlugin({
      key: 'instantsearch',
      limit: 3,
      transformSource({ source }) {
        return {
          ...source,
          getItemUrl({ item }) {
            return getItemUrl({
              query: item.label,
            });
          },
          onSelect({ setIsOpen, setQuery, item, event }) {
            onSelect({
              setQuery,
              setIsOpen,
              event,
              query: item.label,
            });
          },
          // Update the default `item` template to wrap it with a link
          // and plug it to the InstantSearch router.
          templates: {
            ...source.templates,
            item(params) {
              const { children } = source.templates.item(params).props;

              return createItemWrapperTemplate({
                query: params.item.label,
                children,
                html: params.html,
              });
            },
          },
        };
      },
    });
    // end recent
    // build suggess
    function debounce(fn, time) {
      let timerId = undefined;
    
      return function(...args) {
        if (timerId) {
          clearTimeout(timerId);
        }
    
        timerId = setTimeout(() => fn(...args), time);
      }
    }
    
    const debouncedSetInstantSearchUiState = debounce(setInstantSearchUiState, 500);
    const querySuggestionsPlugin = createQuerySuggestionsPlugin({
      searchClient,
      indexName: 'wp_posts_product_query_suggestions',
      getSearchParams() {
        // This creates a shared `hitsPerPage` value once the duplicates
        // between recent searches and Query Suggestions are removed.
        return recentSearchesPlugin.data.getAlgoliaSearchParams({
          hitsPerPage: 6,
        });
      },
      transformSource({ source }) {
        return {
          ...source,
          sourceId: 'querySuggestionsPlugin',
          getItemUrl({ item }) {
            return getItemUrl({
              query: item.query,
            });
          },
          onSelect({ setIsOpen, setQuery, event, item }) {
            onSelect({
              setQuery,
              setIsOpen,
              event,
              query: item.query,
            });
          },
          getItems(params) {
            // We don't display Query Suggestions when there's no query.
            if (!params.state.query) {
              return [];
            }
    
            return source.getItems(params);
          },
          templates: {
            ...source.templates,
            item(params) {
              const { children } = source.templates.item(params).props;
    
              return createItemWrapperTemplate({
                query: params.item.label,
                children,
                html: params.html,
              });
            },
          },
        };
      },
    });
    // Set the InstantSearch index UI state from external events.
    function setInstantSearchUiState(indexUiState) {
      search.setUiState(uiState => ({
        ...uiState,
        [INSTANT_SEARCH_INDEX_NAME]: {
          ...uiState[INSTANT_SEARCH_INDEX_NAME],
          // We reset the page when the search state changes.
          page: 1,
          ...indexUiState,
        },
      }));
    }

    // Return the InstantSearch index UI state.
    function getInstantSearchUiState() {
      const uiState = instantSearchRouter.read();

      return (uiState && uiState[INSTANT_SEARCH_INDEX_NAME]) || {};
    }
    
    const searchPageState = getInstantSearchUiState();

    let skipInstantSearchUiStateUpdate = false;
    const { setQuery } = autocomplete({
      container: is_mobile ? '#searchbox_MB' : '#searchbox',
      showReset: false,
      showSubmit: false,
      debug: true,
      placeholder: 'Search...',
      classNames: {
        input: 'algolia-search__text-field',
      },
      // container: '#autocomplete',
      detachedMediaQuery: 'none',
      openOnFocus: true,
      // Add the recent searches plugin.
      plugins: [recentSearchesPlugin,querySuggestionsPlugin],
      initialState: {
        query: searchPageState.query || '',
      },
      onSubmit({ state }) {
        setInstantSearchUiState({ query: state.query });
      },
      onReset() {
        setInstantSearchUiState({ query: '' });
      },
      onStateChange({ prevState, state }) {
        if (!skipInstantSearchUiStateUpdate && prevState.query !== state.query) {
          debouncedSetInstantSearchUiState({ query: state.query });
        }
        skipInstantSearchUiStateUpdate = false;
      },
      navigator: {
        navigate() {
          // We don't navigate to a new page because we leverage the InstantSearch
          // UI state API.
        },
      },
    })

    // This keeps Autocomplete aware of state changes coming from routing
    // and updates its query accordingly
    window.addEventListener('popstate', () => {
      skipInstantSearchUiStateUpdate = true;
      setQuery(search.helper?.state.query || '');
    });


    const searchResultActive = () => {
      $(document.body).on('Algolia:SearchResultActive', (e, active) => {
          if (active == true) {
              document.body.classList.add('__algolia-search-result-active')
          } else {
            // document.body.classList.remove('__algolia-search-result-active')
          }
      });
    }

    const searchFieldHandle = () => {
        const $input = $('input.algolia-search__text-field');

        $('body').on('input', 'input.algolia-search__text-field', function(e) {
            e.preventDefault();
        })        
        $('body').on('submit', '.aa-Form', function(e) {
            e.preventDefault();
        })

        $(document).on('click', function(e) {
          if ($(e.target).closest(".algolia-search__result-entry").length === 0 && !$(e.target).hasClass('algolia-search__text-field') && !$(e.target).closest('.aa-Panel').length && !$(e.target).closest('.algolia-search-container').length) {
            document.body.classList.remove('__algolia-search-result-active')
          }
        });

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