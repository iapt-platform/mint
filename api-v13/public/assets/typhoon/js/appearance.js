((function(global) {
  const DATA = JSON.parse(document.documentElement.dataset.appearance || 'null');
  const matchMedia = window.matchMedia('(prefers-color-scheme: dark)');

  try {
    // Chrome & Firefox
    matchMedia.addEventListener('change', (event) => {
      if (typhoonRetrieve().appearance === 'system') {
        typhoonSetTheme(event.matches ? 'dark' : 'light', true);
      }
    });
  } catch (fallback) {
    try {
      // Safari
      matchMedia.addListener((event) => {
        if (typhoonRetrieve().appearance === 'system') {
          typhoonSetTheme(event.matches ? 'dark' : 'light', true);
        }
      });
    } catch (error) {
      console.error(error);
    }
  }

  global.typhoonStore = function(data) {
    const STORAGE = DATA.store ? 'localStorage' : 'sessionStorage';
    const theme = data.appearance === 'system' ? typhoonGetTheme() : data.appearance;
    const config = Object.assign({}, typhoonRetrieve(), data, { theme: theme });
    global[STORAGE].setItem('typhoon-appearance', JSON.stringify(config));
    typhoonSetTheme(theme, false);
  }

  global.typhoonRetrieve = function() {
    const STORAGE = DATA.store ? 'localStorage' : 'sessionStorage';
    const systemTheme = typhoonGetTheme();
    const storage = JSON.parse(global[STORAGE].getItem('typhoon-appearance') || 'null');

    if (storage && storage.appearance === 'system') {
      storage.theme = systemTheme;
    }

    return storage || {
      theme: DATA.appearance === 'system' ? systemTheme : DATA.appearance,
      appearance: DATA.appearance || 'system'
    };
  }

  global.typhoonSetTheme = function(theme, store) {
    const event = new CustomEvent('typhoon-theme', {
      detail: {
        theme: theme,
        appearance: typhoonRetrieve().appearance
      }
    });

    if (store) {
      typhoonStore({ theme: theme || typhoonGetTheme() });
    }

    window.dispatchEvent(event);
  }

  global.typhoonGetTheme = function() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

})(window));
