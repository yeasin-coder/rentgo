(function () {

	"use strict";

	const eventHub = new Vue();

	let jetAbafSettingsPage = {
		methods: {
			saveSettings: function() {
				let self = this;

				jQuery.ajax( {
					url: ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'jet_abaf_save_settings',
						settings: this.settings,
						nonce: window?.JetABAFConfig?.nonce
					},
				} ).done( function( response ) {
					if ( response.success ) {
						self.$CXNotice.add( {
							message: response.data.message,
							type: 'success',
							duration: 7000,
						} );
					}
				} ).fail( function( jqXHR, textStatus, errorThrown ) {
					self.$CXNotice.add( {
						message: errorThrown,
						type: 'error',
						duration: 15000,
					} );
				} );
			}
		}
	};

	Vue.component( 'jet-abaf-settings-general', {
		template: '#jet-abaf-settings-general',
		props: {
			settings: {
				type: Object,
				default: {},
			}
		},
		data: function() {
			return {
				postTypes: window.JetABAFConfig.post_types,
				generalSettings: {}
			};
		},
		mounted: function() {
			this.generalSettings = this.settings;
		},
		methods: {
			updateSetting: function( value, key ) {
				this.$emit( 'force-update', {
					key: key,
					value: value,
				} );
			}
		}
	} );

	Vue.component( 'jet-abaf-settings-labels', {
		template: '#jet-abaf-settings-labels',
		props: {
			settings: {
				type: Object,
				default: {},
			}
		},
		data: function() {
			return {
				advancedSettings: {}
			};
		},
		mounted: function() {
			this.advancedSettings = this.settings;
		},
		methods: {
			updateSetting: function( value, key ) {
				this.$emit( 'force-update', {
					key: key,
					value: value,
				} );
			}
		}
	} );

	Vue.component( 'jet-abaf-settings-advanced', {
		template: '#jet-abaf-settings-advanced',
		props: {
			settings: {
				type: Object,
				default: {},
			}
		},
		data: function() {
			return {
				advancedSettings: {},
				cronSchedules: window.JetABAFConfig.cron_schedules,
			};
		},
		mounted: function() {
			this.advancedSettings = this.settings;
		},
		methods: {
			getInterval: function( to ) {

				var res = [];

				for ( var i = 0; i <= to; i++ ) {

					let item = {};
					let val  = '';

					if ( 10 > i ) {
						val = '' + '0' + i;
					} else {
						val = i;
					}

					item.value = val;
					item.label = val;

					res.push( item );
				}

				return res;

			},
			updateSetting: function( value, key ) {
				this.$emit( 'force-update', {
					key: key,
					value: value,
				} );
			}
		}
	} );

	Vue.component( 'jet-abaf-settings-configuration', {
		template: '#jet-abaf-settings-configuration',
		props: {
			settings: {
				type: Object,
				default: {},
			}
		},
		data: function() {
			return {
				configurationSettings: {}
			};
		},
		mounted: function() {
			this.configurationSettings = this.settings;

			this.$nextTick( function () {
				eventHub.$on( 'update-settings', this.updateSetting );
			} );
		},
		methods: {
			updateSetting: function( value, key ) {
				this.$emit( 'force-update', {
					key: key,
					value: value,
				} );
			}
		}
	} );

	Vue.component( 'jet-abaf-settings-common-config', {
		template: '#jet-abaf-settings-common-config',
		props: {
			settings: {
				type: Object,
				default: {},
			}
		},
		methods: {
			updateSetting: function( value, key ) {
				this.$nextTick( function() {
					eventHub.$emit( 'update-settings', value, key );
				} );
			}
		}
	} );

	Vue.component( 'jet-abaf-settings-schedule', {
		template: '#jet-abaf-settings-schedule',
		mixins: [ jetAbafSettingsPage, scheduleManager, dateMethods ],
		props: {
			settings: {
				type: Object,
				default: {},
			}
		},
		methods: {
			updateSetting: function( value, key ) {
				this.$emit( 'force-update', {
					key: key,
					value: value,
				} );
			}
		}
	} );

	Vue.component( 'jet-abaf-settings-tools', {
		template: '#jet-abaf-settings-tools',
		props: {
			settings: {
				type: Object,
				default: {},
			},
			dbTablesExists: {
				type: Boolean
			}
		},
		data: function() {
			return {
				toolsSettings: {},
				processingTables: false
			};
		},
		mounted: function() {
			this.toolsSettings = this.settings;
		},
		methods: {
			processTables: function() {

				let self = this;

				self.processingTables = true;

				jQuery.ajax( {
					url: ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'jet_abaf_process_tables',
						nonce: window?.JetABAFConfig?.nonce
					},
				} ).done( function( response ) {

					self.processingTables = false;

					if ( response.success ) {
						if ( ! self.dbTablesExists ) {
							self.dbTablesExists = true;
						}

						self.$CXNotice.add( {
							message: response.data.message,
							type: 'success',
							duration: 7000,
						} );
					} else {
						self.$CXNotice.add( {
							message: response.data.message,
							type: 'error',
							duration: 15000,
						} );
					}
				} ).fail( function( jqXHR, textStatus, errorThrown ) {

					self.processingTables = false;

					self.$CXNotice.add( {
						message: errorThrown,
						type: 'error',
						duration: 15000,
					} );

				} );

			},
			addNewColumn: function() {

				let col = {
					column: '',
					collapsed: false,
				};

				this.toolsSettings.additional_columns.push( col );

			},
			setColumnProp: function( index, key, value ) {

				let col = this.toolsSettings.additional_columns[ index ];

				col[ key ] = value;

				this.toolsSettings.additional_columns.splice( index, 1, col );
				this.updateSetting( this.toolsSettings.additional_columns, 'additional_columns' );

			},
			cloneColumn: function( index ) {

				let col    = this.toolsSettings.additional_columns[ index ],
					newCol = {
						'column': col.column + '_copy',
					};

				this.toolsSettings.additional_columns.splice( index + 1, 0, newCol );
				this.updateSetting( this.toolsSettings.additional_columns, 'additional_columns' );

			},
			deleteColumn: function( index ) {
				this.toolsSettings.additional_columns.splice( index, 1 );
				this.updateSetting( this.toolsSettings.additional_columns, 'additional_columns' );
			},
			isCollapsed: function( object ) {
				return undefined === object.collapsed || true === object.collapsed;
			},
			updateSetting: function( value, key ) {
				this.$emit( 'force-update', {
					key: key,
					value: value,
				} );
			}
		}
	} );

	new Vue({
		el: '#jet-abaf-settings-page',
		template: '#jet-abaf-settings',
		mixins: [ jetAbafSettingsPage ],
		data: {
			settings: window.JetABAFConfig.settings,
			dbTablesExists: window.JetABAFConfig.db_tables_exists,
		},
		computed: {
			initialTab: function() {

				let result = 'general';

				if ( ! this.dbTablesExists ) {
					result = 'tools';
				}

				return result;

			},
		},
		methods: {
			onUpdateSettings: function( setting, force ) {
				force = force || false;
				this.$set( this.settings, setting.key, setting.value );
				if ( force ) {
					this.$nextTick( function() {
						this.saveSettings();
					} );
				}
			}
		}
	});

})();
