const Ziggy = {
	url: 'http:\/\/inventory-system.test',
	port: null,
	defaults: {},
	routes: {
		login: { uri: 'login', methods: ['GET', 'HEAD'] },
		'login.attempt': { uri: 'login', methods: ['POST'] },
		logout: { uri: 'logout', methods: ['POST'] },
		'password.confirm': { uri: 'user\/confirm-password', methods: ['GET', 'HEAD'] },
		'password.confirmation': {
			uri: 'user\/confirmed-password-status',
			methods: ['GET', 'HEAD']
		},
		'password.confirm.store': { uri: 'user\/confirm-password', methods: ['POST'] },
		'two-factor.login': { uri: 'two-factor-challenge', methods: ['GET', 'HEAD'] },
		'two-factor.login.store': { uri: 'two-factor-challenge', methods: ['POST'] },
		'two-factor.enable': { uri: 'user\/two-factor-authentication', methods: ['POST'] },
		'two-factor.confirm': {
			uri: 'user\/confirmed-two-factor-authentication',
			methods: ['POST']
		},
		'two-factor.disable': { uri: 'user\/two-factor-authentication', methods: ['DELETE'] },
		'two-factor.qr-code': { uri: 'user\/two-factor-qr-code', methods: ['GET', 'HEAD'] },
		'two-factor.secret-key': { uri: 'user\/two-factor-secret-key', methods: ['GET', 'HEAD'] },
		'two-factor.recovery-codes': {
			uri: 'user\/two-factor-recovery-codes',
			methods: ['GET', 'HEAD']
		},
		'two-factor.regenerate-recovery-codes': {
			uri: 'user\/two-factor-recovery-codes',
			methods: ['POST']
		},
		'items.index': { uri: 'items', methods: ['GET', 'HEAD'] },
		'items.store': { uri: 'items', methods: ['POST'] },
		'items.update': {
			uri: 'items\/{item}',
			methods: ['PUT'],
			parameters: ['item'],
			bindings: { item: 'id' }
		},
		'items.destroy': {
			uri: 'items\/{item}',
			methods: ['DELETE'],
			parameters: ['item'],
			bindings: { item: 'id' }
		},
		'categories.index': { uri: 'categories', methods: ['GET', 'HEAD'] },
		'categories.store': { uri: 'categories', methods: ['POST'] },
		'categories.update': {
			uri: 'categories\/{category}',
			methods: ['PUT', 'PATCH'],
			parameters: ['category'],
			bindings: { category: 'id' }
		},
		'categories.destroy': {
			uri: 'categories\/{category}',
			methods: ['DELETE'],
			parameters: ['category'],
			bindings: { category: 'id' }
		},
		'storage.local': {
			uri: 'storage\/{path}',
			methods: ['GET', 'HEAD'],
			wheres: { path: '.*' },
			parameters: ['path']
		}
	}
};
if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
	Object.assign(Ziggy.routes, window.Ziggy.routes);
}
export { Ziggy };
