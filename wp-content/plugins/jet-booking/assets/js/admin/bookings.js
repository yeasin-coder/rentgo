(function () {

	"use strict";

	const eventHub = new Vue();
	const { __, sprintf } = wp.i18n;
	const buildQuery = function( params ) {
		return Object.keys( params ).map( function( key ) {
			return key + '=' + params[ key ];
		} ).join( '&' );
	}

	window.jetBookingState = {
		isActive: false
	};

	const getCurrentMode = function () {
		let mode = window.location.hash ? window.location.hash.replace( '#', '' ) : false;

		if ( ! mode ) {
			return 'all';
		}

		return [ 'all', 'upcoming', 'past' ].includes( mode ) ? mode : 'all';
	}

	// Mixin for handling booking fields.
	const fieldsManager = {
		computed: Vuex.mapState( {
			bookingItem: 'bookingItem',
			dateRangePickerConfig: 'dateRangePickerConfig',
			isDisabled: 'isDisabled',
			itemUnits: 'itemUnits'
		} ),
		methods: {
			initDateRangePicker: function () {

				let self = this;

				store.dispatch( 'getDateRangePickerConfig' ).then( function () {
					jQuery( self.$refs.jetABAFDatePicker ).dateRangePicker( self.dateRangePickerConfig )
						.bind( 'datepicker-first-date-selected', () => {
							window.jetBookingState.isActive = true;
						} ).bind( 'datepicker-change', () => {
							window.jetBookingState.isActive = false;
						} );
				} );

			},
			beVisible: function ( key ) {
				switch ( key ) {
					case 'booking_id':
					case 'apartment_unit':
					case 'order_id':
					case 'import_id':
					case 'status':
					case 'apartment_id':
					case 'check_in_date':
					case 'check_in_date_timestamp':
					case 'check_out_date':
					case 'check_out_date_timestamp':
						return false;
					default:
						return true;
				}
			},
			onApartmentChange: function () {
				this.initDateRangePicker();
			},
		}
	};

	const store = new Vuex.Store( {
		state: {
			...window.JetABAFConfig,
			perPage: 15,
			offset: 0,
			pageNumber: 1,
			totalItems: 0,
			itemsList: [],
			isLoading: true,
			overlappingBookings: false,
			bookingItem: {},
			dateRangePickerConfig: {},
			isDisabled: false,
			itemUnits: [],
			currentFilters: {},
			sortBy: {
				orderby: 'booking_id',
				order: 'DESC'
			}
		},
		mutations: {
			setValue( state, varObject ) {
				state[ varObject.key ] = varObject.value;
			},
		},
		actions: {
			getItems: function() {

				store.commit( 'setValue', {
					key: 'isLoading',
					value: true
				} );

				wp.apiFetch( {
					method: 'get',
					path: window.JetABAFConfig.api.bookings_list + '?' + buildQuery( {
						per_page: store.state.perPage,
						offset: store.state.offset,
						filters: JSON.stringify( store.state.currentFilters ),
						sort: JSON.stringify( store.state.sortBy ),
						mode: getCurrentMode()
					} ),
				} ).then( function( response ) {

					store.commit( 'setValue', {
						key: 'isLoading',
						value: false
					} );

					if ( response.success ) {
						store.commit( 'setValue', {
							key: 'itemsList',
							value: response.data
						} );

						store.commit( 'setValue', {
							key: 'totalItems',
							value: response.total
						} );
					}

				} ).catch( function( e ) {

					store.commit( 'setValue', {
						key: 'isLoading',
						value: false
					} );

					eventHub.$CXNotice.add( {
						message: e.message,
						type: 'error',
						duration: 7000,
					} );

				} );

			},
			getDateRangePickerConfig: async function() {

				store.commit( 'setValue', {
					key: 'isDisabled',
					value: true
				} );

				const bookingItem = store.state.bookingItem;

				if ( ! bookingItem.check_in_date ) {
					bookingItem.check_in_date = '';
				}

				if ( ! bookingItem.check_out_date ) {
					bookingItem.check_out_date = '';
				}

				await wp.apiFetch( {
					method: 'post',
					path: window.JetABAFConfig.api.booked_dates,
					data: { item: bookingItem }
				} ).then( function( response ) {
					if ( ! response.success ) {
						eventHub.$CXNotice.add( {
							message: response.data,
							type: 'error',
							duration: 7000,
						} );
					} else {
						const
							perNights = response.per_nights,
							excludedDates = response.booked_dates,
							daysOff = response.days_off,
							disabledDays = response.disabled_days,
							checkInDays = response.check_in_days,
							checkoutOnly = response.checkout_only,
							excludedNext = response.booked_next,
							labels = response.labels,
							startDayOffset = response.start_day_offset,
							minDays = response.min_days,
							maxDays = response.max_days;

						window.JetABAFConfig.seasonal_price = response.seasonal_price;
						window.JetABAFConfig.start_day_offset = startDayOffset;
						window.JetABAFConfig.min_days = minDays;
						window.JetABAFConfig.max_days = maxDays;
						window.JetABAFConfig.check_in_days = checkInDays;
						window.JetABAFConfig.check_out_days = response.check_out_days;
						window.JetABAFConfig.booked_dates = response.booked_dates;
						window.JetABAFConfig.disabled_days = response.disabled_days;
						window.JetABAFConfig.checkout_only = response.checkout_only;

						if ( bookingItem.check_in_date.length && bookingItem.check_out_date.length ) {
							let deleteCount = moment( bookingItem.check_out_date ).diff( moment( bookingItem.check_in_date ), 'days' );

							if ( ! perNights ) {
								deleteCount++;
							}

							excludedDates.splice( excludedDates.indexOf( bookingItem.check_in_date ), deleteCount );
						}

						let config = {
							autoClose: true,
							separator: ' - ',
							startOfWeek: response.start_of_week,
							getValue: function () {
								if ( bookingItem.check_in_date && bookingItem.check_out_date ) {
									return bookingItem.check_in_date + ' - ' + bookingItem.check_out_date;
								} else {
									return '';
								}
							},
							setValue: function ( s, s1, s2 ) {
								if ( s === s1 ) {
									s2 = s1;
								}

								bookingItem.check_in_date = s1;
								bookingItem.check_out_date = s2;
							},
							startDate: startDayOffset.length && +startDayOffset ? moment().add( +startDayOffset, 'd' ) : new Date(),
							minDays: minDays.length && +minDays ? +minDays : '',
							maxDays: maxDays.length && +maxDays ? +maxDays : '',
							perNights: perNights,
							container: '.jet-abaf-details__booking-dates',
							beforeShowDay: function( t ) {
								let formated = moment( t ).format( 'YYYY-MM-DD' ),
									valid = true,
									_class = '',
									_tooltip = '';

								if ( disabledDays.length && 0 <= disabledDays.indexOf( t.getDay() ) ) {
									valid = false;
								}

								if ( checkInDays.length && -1 === checkInDays.indexOf( t.getDay() ) ) {
									valid = window.jetBookingState.isActive && -1 === disabledDays.indexOf( t.getDay() );
								}

								if ( excludedDates.length && 0 <= excludedDates.indexOf( formated ) ) {
									valid = false;
									_tooltip = labels.booked;

									// Mark first day of booked period as checkout only
									if ( checkoutOnly ) {
										let next = moment( t ).add( 1, 'd' ).format( 'YYYY-MM-DD' );
										let prev = moment( t ).subtract( 1, 'd' ).format( 'YYYY-MM-DD' );

										if ( 0 <= excludedNext.indexOf( next ) || ( 0 <= excludedDates.indexOf( next ) && -1 === excludedDates.indexOf( prev ) ) ) {
											if ( window.jetBookingState.isActive ) {
												valid = true;
												_tooltip = '';
											} else {
												_class = 'only-checkout';
												_tooltip = labels['only-checkout'];
											}
										}
									}
								}

								// If is single night booking - exclude next day for checkout only days.
								if ( checkoutOnly && window.jetBookingState.isActive && 0 <= excludedNext.indexOf( formated ) ) {
									valid = false;
									_tooltip = labels.booked;
								}

								if ( daysOff.length && 0 <= daysOff.indexOf( formated ) ) {
									valid = false;
									_tooltip = '';
								}

								return window.JetPlugins.hooks.applyFilters( 'jet-booking.date-range-picker.date-show-params', [ valid, _class, _tooltip ], t );
							},
							excludedDates: excludedDates,
							selectForward: true,
						};

						if ( response.custom_labels ) {
							jQuery.dateRangePickerLanguages['custom'] = labels;
							config.language = 'custom';
						}

						if ( response.weekly_bookings ) {
							config.batchMode = 'week';
							config.showShortcuts = false;

							if ( response.week_offset ) {
								config.weekOffset = Number( response.week_offset );
							}
						} else if ( response.one_day_bookings ) {
							config.singleDate = true;
						}

						store.commit( 'setValue', {
							key: 'dateRangePickerConfig',
							value: window.JetPlugins.hooks.applyFilters( 'jet-booking.input.config', config )
						} );

						store.commit( 'setValue', {
							key: 'isDisabled',
							value: false
						} );

						store.commit( 'setValue', {
							key: 'itemUnits',
							value: response.units.length ? response.units.map( unit => ({
								value: unit.unit_id,
								label: unit.unit_title
							}) ) : []
						} );
					}

				} ).catch( function( e ) {
					eventHub.$CXNotice.add( {
						message: e.message,
						type: 'error',
						duration: 7000,
					} );
				} );

			}
		}
	} );

	Vue.component( 'jet-abaf-bookings-list', {
		template: '#jet-abaf-bookings-list',
		mixins: [ fieldsManager ],
		data: function() {
			return {
				currentSort: 'booking_id',
				deleteDialog: false,
				deleteItem: false,
				detailsDialog: false,
				currentItem: false,
				currentIndex: false,
				editDialog: false,
			};
		},
		computed: Vuex.mapState( {
			sortBy: 'sortBy',
			itemsList: 'itemsList',
			perPage: 'perPage',
			offset: 'offset',
			pageNumber: 'pageNumber',
			totalItems: 'totalItems',
			statuses: state => state.all_statuses,
			bookingInstances: state => state.bookings,
			overlappingBookings: 'overlappingBookings'
		} ),
		methods: {
			sortColumn: function ( column ) {
				this.currentSort = column;

				store.commit( 'setValue', {
					key: 'sortBy',
					value: {
						orderby: column,
						order: "DESC" === this.sortBy.order ? "ASC" : "DESC"
					}
				} );

				store.dispatch( 'getItems' );
			},
			classColumn: function ( column ) {
				return {
					'jet-abaf-active-column': column === this.currentSort,
					'jet-abaf-active-column-asc': column === this.currentSort && "DESC" === this.sortBy.order,
					'jet-abaf-active-column-desc': column === this.currentSort && "ASC" === this.sortBy.order,
				};
			},
			changePage: function( page ) {

				store.commit( 'setValue', {
					key: 'offset',
					value: this.perPage * ( page - 1 )
				} );

				store.commit( 'setValue', {
					key: 'pageNumber',
					value: page
				} );

				store.dispatch( 'getItems' );

			},
			showEditDialog: function ( item, index ) {

				this.editDialog = true;

				store.commit( 'setValue', {
					key: 'overlappingBookings',
					value: false
				} );

				this.currentItem = JSON.parse( JSON.stringify( item ) );
				this.currentIndex = index;

				this.currentItem.check_in_date = moment.unix( this.currentItem.check_in_date_timestamp ).utc().format( 'YYYY-MM-DD' );
				this.currentItem.check_out_date = moment.unix( this.currentItem.check_out_date_timestamp ).utc().format( 'YYYY-MM-DD' );

				store.commit( 'setValue', {
					key: 'bookingItem',
					value: this.currentItem
				} );

				this.initDateRangePicker();

			},
			showDetailsDialog: function( item ) {
				this.detailsDialog = true;
				this.currentItem = item;
			},
			showDeleteDialog: function( itemID ) {
				this.deleteItem = itemID;
				this.deleteDialog = true;
			},
			deleteDetailsItem: function ( itemID ) {
				this.detailsDialog = false;
				this.showDeleteDialog( itemID );
			},
			editDetailsItem: function ( item ) {
				this.detailsDialog = false;
				this.showEditDialog( item );
			},
			handleEdit: function() {

				let self = this;

				if ( ! self.currentItem ) {
					return;
				}

				store.commit( 'setValue', {
					key: 'overlappingBookings',
					value: false
				} );

				if ( ! self.itemUnits.length ) {
					self.currentItem.apartment_unit = null;
				}

				wp.apiFetch( {
					method: 'post',
					path: window.JetABAFConfig.api.update_booking + self.currentItem.booking_id + '/',
					data: { item: self.currentItem }
				} ).then( function( response ) {

					if ( ! response.success ) {
						if ( response.overlapping_bookings ) {
							self.$CXNotice.add( {
								message: response.data,
								type: 'error',
								duration: 7000,
							} );

							store.commit( 'setValue', {
								key: 'overlappingBookings',
								value: response.html
							} );

							self.editDialog = true;

							return;
						} else {
							self.$CXNotice.add( {
								message: response.data,
								type: 'error',
								duration: 7000,
							} );
						}
					} else {
						self.$CXNotice.add( {
							message: 'Done!',
							type: 'success',
							duration: 7000,
						} );

						store.dispatch( 'getItems' );
					}

					self.currentItem = false;
					self.currentIndex = false;

				} ).catch( function( e ) {

					self.$CXNotice.add( {
						message: e.message,
						type: 'error',
						duration: 7000,
					} );

					self.currentItem = false;
					self.currentIndex = false;

				} );

			},
			handleDelete: function() {

				var self = this;

				if ( ! self.deleteItem ) {
					return;
				}

				wp.apiFetch( {
					method: 'delete',
					path: window.JetABAFConfig.api.delete_booking + self.deleteItem + '/',
				} ).then( function( response ) {
					if ( ! response.success ) {
						self.$CXNotice.add( {
							message: response.data,
							type: 'error',
							duration: 7000,
						} );
					}

					for ( var i = 0; i < self.itemsList.length; i++ ) {
						if ( self.itemsList[ i ].booking_id === self.deleteItem ) {
							self.itemsList.splice( i, 1 );
							break;
						}
					}

				} ).catch( function( e ) {
					self.$CXNotice.add( {
						message: e.message,
						type: 'error',
						duration: 7000,
					} );
				} );
			},
			getBookingLabel: function( id ) {

				if ( ! id ) {
					return '--';
				}

				return this.bookingInstances[ id ] || id;

			},
			getOrderLink: function( orderID ) {
				return window.JetABAFConfig.edit_link.replace( /\%id\%/, orderID );
			},
			isFinished: function( status ) {
				return ( 0 <= window.JetABAFConfig.statuses.finished.indexOf( status ) );
			},
			isInProgress: function( status ) {
				return ( 0 <= window.JetABAFConfig.statuses.in_progress.indexOf( status ) );
			},
			isInvalid: function( status ) {
				return ( 0 <= window.JetABAFConfig.statuses.invalid.indexOf( status ) );
			}
		},
	} );

	Vue.component( 'jet-abaf-bookings-filter', {
		template: '#jet-abaf-bookings-filter',
		mixins: [ fieldsManager ],
		components: {
			vuejsDatepicker: window.vuejsDatepicker,
		},
		data() {
			return {
				currentMode: 'all',
				expandFilters: false,
				dateFormat: 'dd/MM/yyyy',
			}
		},
		computed: {
			...Vuex.mapState( [ 'filters', 'currentFilters', 'monday_first' ] ),
		},
		created: function() {
			this.currentMode = getCurrentMode();
		},
		methods: {
			setMode( mode ) {
				window.location.hash = '#' + mode;
				this.currentMode = mode;

				store.commit( 'setValue', {
					key: 'offset',
					value: 0
				} );

				store.commit( 'setValue', {
					key: 'pageNumber',
					value: 1
				} );

				store.dispatch( 'getItems' );
			},
			modeButtonStyle( mode ) {
				return this.currentMode === mode ? 'accent' : 'link-accent';
			},
			updateFilters: function ( value, name, type ) {
				let filterValue = value.target ? value.target.value : value;
				let currentFilter = {};

				if ( 'date-picker' === type ) {
					filterValue = value ? moment( filterValue ).format( 'MMMM DD YYYY' ) : '';
				}

				if ( filterValue.length ) {
					currentFilter = { [ name ]: filterValue };
				} else {
					delete this.currentFilters[ name ];
				}

				store.commit( 'setValue', {
					key: 'currentFilters',
					value: Object.assign( {}, this.currentFilters, currentFilter )
				} );

				store.commit( 'setValue', {
					key: 'offset',
					value: 0
				} );

				store.commit( 'setValue', {
					key: 'pageNumber',
					value: 1
				} );

				store.dispatch( 'getItems' );
			},
			clearFilter: function () {
				store.commit( 'setValue', {
					key: 'currentFilters',
					value: {}
				} );

				store.commit( 'setValue', {
					key: 'offset',
					value: 0
				} );

				store.commit( 'setValue', {
					key: 'pageNumber',
					value: 1
				} );

				store.dispatch( 'getItems' );
			},
			isVisible( id, filter, type ) {
				if ( type !== filter.type ) {
					return false;
				}

				if ( 'select' === filter.type && ! Object.keys( filter.value ).length ) {
					return false;
				}

				return true;
			},
			prepareObjectForOptions: function ( input ) {
				let result = [ {
					'value': '',
					'label': wp.i18n.__( 'Select...', 'jet-booking' ),
				} ];

				for ( const value in input ) {
					if ( input.hasOwnProperty( value ) ) {
						result.push( {
							'value': value,
							'label': input[ value ],
						} );
					}
				}

				return result;
			},
		}
	} );

	Vue.component( 'jet-abaf-add-new-booking', {
		template: '#jet-abaf-add-new-booking',
		mixins: [ fieldsManager ],
		data: function () {
			return {
				addDialog: false,
				newItem: {
					status: '',
					apartment_id: '',
					check_in_date: '',
					check_out_date: '',
				},
				datePickerFormat: 'dd-MM-yyyy',
				dateMomentFormat: 'DD-MM-YYYY',
				createOrder: false,
				bookingOrderStatus: 'draft',
				wcOrderFirstName: '',
				wcOrderLastName: '',
				wcOrderEmail: '',
				wcOrderPhone: '',
			}
		},
		computed: Vuex.mapState( {
			statuses: state => state.all_statuses,
			bookingInstances: state => state.bookings,
			orderPostType: state => state.order_post_type,
			orderPostTypeStatuses: state => state.order_post_type_statuses,
			wcIntegration: state => state.wc_integration,
			overlappingBookings: 'overlappingBookings',
			fields: function ( state ) {
				return [ ...state.columns, ...state.additional_columns ];
			}
		} ),
		methods: {
			showAddDialog: function() {
				this.addDialog = true;

				store.commit( 'setValue', {
					key: 'overlappingBookings',
					value: false
				} );

				store.commit( 'setValue', {
					key: 'isDisabled',
					value: true
				} );

				store.commit( 'setValue', {
					key: 'bookingItem',
					value: this.newItem
				} );
			},

			checkRequiredFields: function () {
				let requiredFields = [ 'status', 'apartment_id', 'check_in_date', 'check_out_date' ],
					emptyFields = [],
					invalidFields = [],
					message = '';

				for ( let field of requiredFields ) {
					if ( ! this.newItem[ field ].length ) {
						emptyFields.push( field );
					}
				}

				if ( this.wcIntegration && this.createOrder ) {
					if ( ! this.wcOrderFirstName.length ) {
						emptyFields.push( 'wc_order_first_name' );
					}

					if ( ! this.wcOrderLastName.length ) {
						emptyFields.push( 'wc_order_last_name' );
					}

					if ( ! this.wcOrderEmail.length ) {
						emptyFields.push( 'wc_order_email' );
					} else {
						if ( ! this.wcOrderEmail.match( /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/ ) ) {
							invalidFields.push( 'wc_order_email' );
						}
					}
				}

				if ( ! emptyFields.length && ! invalidFields.length ) {
					return true;
				} else if ( emptyFields.length ) {
					emptyFields = emptyFields.join( ', ' ).toLowerCase();
					message = sprintf( __( 'Empty fields: %s.', 'jet-booking' ), emptyFields );
				} else if ( invalidFields.length ) {
					invalidFields = invalidFields.join( ', ' ).toLowerCase();
					message = sprintf( __( 'Invalid value fields: %s.', 'jet-booking' ), invalidFields );
				}

				eventHub.$CXNotice.add( {
					message: message,
					type: 'error',
					duration: 7000,
				} );

				return false;
			},

			handleAdd: function() {

				let self = this;

				if( ! self.checkRequiredFields() ) {
					this.addDialog = true;
					return;
				}

				store.commit( 'setValue', {
					key: 'overlappingBookings',
					value: false
				} );

				const data = {
					item: self.newItem
				}

				if ( self.createOrder ) {
					if ( self.wcIntegration ) {
						data.order = {
							firstName: self.wcOrderFirstName,
							lastName: self.wcOrderLastName,
							email: self.wcOrderEmail,
							phone: self.wcOrderPhone,
						}
					} else if ( self.orderPostType  ) {
						data.order = {
							orderStatus: self.bookingOrderStatus
						};
					}
				}

				wp.apiFetch( {
					method: 'post',
					path: window.JetABAFConfig.api.add_booking,
					data: data
				} ).then( function( response ) {

					if ( ! response.success ) {
						if ( response.overlapping_bookings ) {
							eventHub.$CXNotice.add( {
								message: response.data,
								type: 'error',
								duration: 7000,
							} );

							store.commit( 'setValue', {
								key: 'overlappingBookings',
								value: response.html
							} );

							self.addDialog = true;

							return;
						} else {
							eventHub.$CXNotice.add( {
								message: response.data,
								type: 'error',
								duration: 7000,
							} );
						}
					} else {
						eventHub.$CXNotice.add( {
							message: 'Done!',
							type: 'success',
							duration: 7000,
						} );
					}

					store.dispatch( 'getItems' );

					self.newItem = {
						status: '',
						apartment_id: '',
						check_in_date: '',
						check_out_date: '',
					};

					self.createOrder = false;
					self.bookingOrderStatus = 'draft';
					self.wcOrderFirstName = '';
					self.wcOrderLastName = '';
					self.wcOrderEmail = '';
					self.wcOrderPhone = '';

				} ).catch( function( e ) {
					eventHub.$CXNotice.add( {
						message: e.message,
						type: 'error',
						duration: 7000,
					} );
				} );

			}
		}
	} );

	new Vue( {
		el: '#jet-abaf-bookings-page',
		template: '#jet-abaf-bookings',
		store,
		computed: Vuex.mapState( {
			isSet: state => state.setup.is_set,
			isLoading: 'isLoading',
		} ),
		created: function () {
			store.dispatch('getItems');
		},
	} );

} )();
