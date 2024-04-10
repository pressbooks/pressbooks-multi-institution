<div class="wrap">
	<div class="pb-dashboard-row">
			<div class="pb-dashboard-grid">
				<div class="pb-dashboard-panel">
					<div class="pb-dashboard-content pb-institution-welcome-content">
						<h1 class="screen-reader-text">{{ __('Institutional Manager Dashboard', 'pressbooks-multi-institution')  }}</h1>
						<h2>
							{{ __( 'Welcome to', 'pressbooks-multi-institution' ) }}
							<span class="network-title">{!! $network_name !!}</span>
						</h2>
						<a class="visit-homepage" href="{{ $network_url }}">
							{{ __( 'Visit network homepage', 'pressbooks-multi-institution' ) }}
						</a>
						<p>
							{!! sprintf( __( '%s has %s books and %s users. ', 'pressbooks-multi-institution' ), "{$institution_name}","<strong>{$total_books}</strong>", "<strong>{$total_users}</strong>" ) !!}
						</p>
					</div>
				</div>
				<div class="pb-dashboard-panel">
					<div class="pb-dashboard-content">
						<h2>{{ __( 'Administer', 'pressbooks-multi-institution' )}} {{$institution_name}}</h2>

						<div class="pb-dashboard-flex">
							<img
								class="pb-dashboard-flex-image"
								src="{{ PB_PLUGIN_URL . "assets/dist/images/pb-network-settings.png" }}"
								alt="{{ __( 'Administer network art', 'pressbooks-multi-institution' ) }}"
							/>

							<ul class="actions">
								<li>
									<a
										href="{!! network_admin_url( $network_analytics_active ? 'sites.php?page=pb_network_analytics_booklist' : 'sites.php' ) !!}"
									>
										<i class="pb-heroicons pb-heroicons-book-open" aria-hidden="true"></i>
										<span>{{ __( 'View book list', 'pressbooks-multi-institution' ) }}</span>
									</a>
								</li>
								<li>
									<a
										href="{!! network_admin_url( $network_analytics_active ? 'users.php?page=pb_network_analytics_userlist' : 'users.php' ) !!}"
									>
										<i class="pb-heroicons pb-heroicons-users" aria-hidden="true"></i>
										<span>{{ __( 'View user list', 'pressbooks-multi-institution' ) }}</span>
									</a>
								</li>
								@if( $network_analytics_active )
								<li>
									<a
										href="{!! network_admin_url( 'admin.php?page=pb_network_analytics_admin' ) !!}"
									>
										<i class="pb-heroicons dashicons-before dashicons-chart-area" aria-hidden="true"></i>
										<span>{{ __( 'Explore Stats', 'pressbooks-multi-institution' ) }}</span>
									</a>
								</li>
								@endif
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	@include('admin.dashboard.support')
</div>
