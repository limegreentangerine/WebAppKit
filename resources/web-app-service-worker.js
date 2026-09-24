self.addEventListener('activate', (e) => {
	let promises = [];
	console.log('clear badge');
	if ('setAppBadge' in self.navigator) {
		promises.push(self.navigator.clearAppBadge());
	}
	e.waitUntil(Promise.all(promises));
});

self.addEventListener('push', (e) => {
	let promises = [];

	const data = e.data.json();
	const options = {
		body: data.body,
		icon: data.icon,
		vibrate: [300, 100, 100, 100, 300],
		timestamp: Date.now(),
		data: {
			url: data.data.link_url
		}
	};

	if ('setAppBadge' in self.navigator) {
		const promise = self.navigator.setAppBadge(1);
		promises.push(promise);
	}

	promises.push(self.registration.showNotification(data.title, options));

	e.waitUntil(Promise.all(promises));
});

self.addEventListener('notificationclick', (e) => {
	let promises = [];

	let url = e.notification.data.url;
	e.notification.close(); // Android needs explicit close.

	if ('setAppBadge' in self.navigator) {
		promises.push(self.navigator.clearAppBadge());
	}

	promises.push(
		clients.matchAll({ type: 'window' }).then((windowClients) => {
			// Check if there is already a window/tab open with the target URL
			for (var i = 0; i < windowClients.length; i++) {
				var client = windowClients[i];
				// If so, just focus it.
				if (client.url === url && 'focus' in client) {
					return client.focus();
				}
			}

			// If not, then open the target URL in a new window/tab.
			if (clients.openWindow) {
				return clients.openWindow(url);
			}
		})
	);

	e.waitUntil(Promise.all(promises));
});

self.addEventListener('notificationclose', (e) => {
	let promises = [];
	if ('setAppBadge' in self.navigator) {
		promises.push(self.navigator.clearAppBadge());
	}
	e.waitUntil(Promise.all(promises));
});
