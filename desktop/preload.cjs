const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('electronAPI', {
    minimize: () => ipcRenderer.send('window-minimize'),
    close: () => ipcRenderer.send('window-close'),
    setWindowSize: (width, height) => ipcRenderer.send('set-window-size', { width, height }),
    setAlwaysOnTop: (flag) => ipcRenderer.send('set-always-on-top', flag),
    onDeepLink: (callback) => {
        ipcRenderer.on('deep-link', (_event, url) => callback(url));
    },
    platform: process.platform
});
