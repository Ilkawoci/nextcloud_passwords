WebAssembly is a type of code that can be run in modern web browsers — 
it is a low-level assembly-like language with a compact binary format that runs with near-native performance
and provides languages such as C/C++, C# and Rust with a compilation target so that they can run on the web.
It is also designed to run alongside JavaScript, allowing both to work together.
([From mdn web docs](https://developer.mozilla.org/en-US/docs/WebAssembly))

The Passwords App uses WebAssembly to provide strong end-to-end encryption using the well established libsodium encryption library.
This allows us to use an encryption that is relatively easy and similar to implement across multiple platforms,
making the app more open to developers all around the world and less likely to have flaws in the implementation or handling of the encryption.

WebAssembly is usually enabled by default in all modern browsers, but some privacy focussed users and browser developers enable this feature.
This guide explains how to enable WebAssembly in the most common browser types.


## Chrome and derivates
These instructions should work for Chrome and other all browsers built on it, such as Microsoft Edge, Vivaldi, Brave, Chromium and Opera.

#### Windows
###### Desktop Icon
1. Right-click the application icon
2. Select the "Properties" in the right click menu
3. Remove the option `--js-flags=--noexpose_wasm` from the field "Target"
4. Restart the browser

###### Start Menu
1. Right-click the application icon
2. Select "More" and then "Open file location"
3. Select the "Properties" in the right click menu
4. Remove the option `--js-flags=--noexpose_wasm` from the field "Target"
5. Restart the browser

#### Any other OS
Modify the start command for the browser and remove the `--js-flags=--noexpose_wasm` flag.
Under Linux, apps like [alacarte](https://gitlab.gnome.org/GNOME/alacarte) and [LibreMenuEditor](https://flathub.org/apps/page.codeberg.libre_menu_editor.LibreMenuEditor)
can be used to modify the command for the application shortcut in the start menu.


## Firefox and derivates
These instructions should work for Firefox and other all browsers built on it, such as Tor Browser, Librewolf, Pale Moon, Waterfox and IceCat.

1. Open a new tab and enter the url `about:config`
2. Confirm the warning message
3. Search for "javascript.options.wasm"
4. Set the option "javascript.options.wasm" to true
5. Ensure all other "javascript.options.wasm_*" settings are set to default (Changed settings are highlighted in bold)