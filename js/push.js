const pushSubscribeNotificationTemplate = document.getElementById('push-subscribe-notification');
const swUrl = pushSubscribeNotificationTemplate.dataset.sw;
const publicVapidKey = pushSubscribeNotificationTemplate.dataset.key;
let clientSubscribed = false;

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register(swUrl, {
        scope: '/',
    });
}

function debounce(func, timeout = 300) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => { func.apply(this, args); }, timeout);
    };
};

// Copied from the web-push documentation
const urlBase64ToUint8Array = (base64String) => {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
};

window.subscribe = async () => {
    if (!('serviceWorker' in navigator)) return;
    if (clientSubscribed) return;

    const registration = await navigator.serviceWorker.ready;

    // Subscribe to push notifications
    const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(publicVapidKey),
    });

    const response = await fetch('/push/subscribe', {
        method: 'POST',
        body: JSON.stringify(subscription),
        headers: {
            'content-type': 'application/json'
        },
    });

    return response.success;
};

window.popup = async () => {
    if (!('serviceWorker' in navigator)) return

    const registration = await navigator.serviceWorker.ready;
    registration.pushManager.getSubscription()
        .then((subscription) => {
            if (!subscription) {
                document.body.classList.add('show-push-subscribe-notification');
            }
        });
};

document.addEventListener('click', () => {
    clientSubscribed = subscribe();
});
