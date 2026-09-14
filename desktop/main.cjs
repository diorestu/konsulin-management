const { app, BrowserWindow, ipcMain, Tray, Menu } = require('electron');
const path = require('path');

let mainWindow = null;
let tray = null;
let pendingDeepLinkUrl = null;

// Register default protocol client for konsulin://
if (process.defaultApp) {
    if (process.argv.length >= 2) {
        app.setAsDefaultProtocolClient('konsulin', process.execPath, [path.resolve(process.argv[1])]);
    }
} else {
    app.setAsDefaultProtocolClient('konsulin');
}

// Single instance lock
const gotTheLock = app.requestSingleInstanceLock();
if (!gotTheLock) {
    app.quit();
} else {
    app.on('second-instance', (event, commandLine) => {
        if (mainWindow) {
            if (mainWindow.isMinimized()) mainWindow.restore();
            mainWindow.focus();
            
            // On Windows, deep link URL is passed in commandLine
            const url = commandLine.find(arg => arg.startsWith('konsulin://'));
            if (url) {
                mainWindow.webContents.send('deep-link', url);
            }
        }
    });
}

function createWindow() {
    mainWindow = new BrowserWindow({
        width: 360,
        height: 180,
        minWidth: 300,
        minHeight: 52,
        frame: false,
        transparent: true,
        alwaysOnTop: true,
        skipTaskbar: false,
        hasShadow: true,
        resizable: true,
        webPreferences: {
            preload: path.join(__dirname, 'preload.cjs'),
            contextIsolation: true,
            nodeIntegration: false,
        }
    });

    // Highest always-on-top level across OSes
    if (process.platform === 'darwin') {
        mainWindow.setAlwaysOnTop(true, 'floating', 1);
        mainWindow.setVisibleOnAllWorkspaces(true, { visibleOnFullScreen: true });
    } else {
        mainWindow.setAlwaysOnTop(true, 'screen-saver');
    }

    mainWindow.loadFile(path.join(__dirname, 'index.html'));

    mainWindow.webContents.on('did-finish-load', () => {
        if (pendingDeepLinkUrl) {
            mainWindow.webContents.send('deep-link', pendingDeepLinkUrl);
            pendingDeepLinkUrl = null;
        }
    });

    mainWindow.on('closed', () => {
        mainWindow = null;
    });
}

// macOS deep link handler
app.on('open-url', (event, url) => {
    event.preventDefault();
    if (mainWindow && mainWindow.webContents) {
        mainWindow.webContents.send('deep-link', url);
        mainWindow.show();
        mainWindow.focus();
    } else {
        pendingDeepLinkUrl = url;
    }
});

// IPC Window Controls
ipcMain.on('window-minimize', () => {
    if (mainWindow) mainWindow.minimize();
});

ipcMain.on('window-close', () => {
    if (mainWindow) mainWindow.close();
});

ipcMain.on('set-window-size', (_event, { width, height }) => {
    if (mainWindow) {
        mainWindow.setSize(width, height);
    }
});

ipcMain.on('set-always-on-top', (_event, flag) => {
    if (mainWindow) {
        if (process.platform === 'darwin') {
            mainWindow.setAlwaysOnTop(flag, 'floating', 1);
        } else {
            mainWindow.setAlwaysOnTop(flag, 'screen-saver');
        }
    }
});

app.whenReady().then(() => {
    createWindow();

    app.on('activate', () => {
        if (BrowserWindow.getAllWindows().length === 0) {
            createWindow();
        }
    });
});

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') {
        app.quit();
    }
});
