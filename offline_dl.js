// offline_dl.js
// Stores PDFs as Blob objects in IndexedDB

const DB_NAME = 'StudiesDreamDB';
const STORE_NAME = 'pdf_downloads';

function initDB() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, 1);
        req.onupgradeneeded = e => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains(STORE_NAME)) {
                db.createObjectStore(STORE_NAME, { keyPath: 'id' });
            }
        };
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

window.downloadPDF = async function(id, title, url, btnElement) {
    if(!url || Number(url) === 0 || url === 'undefined') {
        alert("Invalid URL for download.");
        return;
    }
    const icon = btnElement.querySelector('i');
    if(icon) {
        icon.className = 'fas fa-spinner fa-spin';
    }
    
    try {
        let isGDrive = url.includes('drive.google.com');
        let fetchUrl = url;
        
        // Handle GDrive redirect or proxy if needed, 
        // usually we can't cors-fetch GDrive easily from frontend.
        // We'll proxy through PHP for actual blob download.
        const res = await fetch(`fetch_pdf.php?url=${encodeURIComponent(url)}`);
        
        if(!res.ok) throw new Error("Failed to fetch file");
        
        const blob = await res.blob();
        
        const db = await initDB();
        const tx = db.transaction(STORE_NAME, 'readwrite');
        const store = tx.objectStore(STORE_NAME);
        
        const record = {
            id: 'pdf_' + id + '_' + Date.now().toString(),
            title: title || 'Document',
            blob: blob,
            timestamp: new Date().getTime(),
            original_url: url
        };
        
        store.put(record);
        
        tx.oncomplete = () => {
            if(icon) icon.className = 'fas fa-check text-green-500';
            alert("File downloaded securely for offline viewing!");
        };
    } catch(err) {
        console.error(err);
        if(icon) icon.className = 'fas fa-download';
        alert("Could not download file. It might be blocked by CORS if it's a direct Google Drive link.");
    }
};

window.getDownloadedPDFs = async function() {
    const db = await initDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE_NAME, 'readonly');
        const store = tx.objectStore(STORE_NAME);
        const req = store.getAll();
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
};

window.deleteDownloadedPDF = async function(id) {
    const db = await initDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE_NAME, 'readwrite');
        const store = tx.objectStore(STORE_NAME);
        const req = store.delete(id);
        req.onsuccess = () => resolve(true);
        req.onerror = () => reject(req.error);
    });
};

window.openOfflinePDF = async function(id) {
    const db = await initDB();
    const tx = db.transaction(STORE_NAME, 'readonly');
    const store = tx.objectStore(STORE_NAME);
    const req = store.get(id);
    
    req.onsuccess = () => {
        if(req.result && req.result.blob) {
            const blobUrl = URL.createObjectURL(req.result.blob);
            window.location.href = `pdf_viewer.php?url=${encodeURIComponent(blobUrl)}&offline=true`;
        } else {
            alert('File not found locally.');
        }
    };
};
