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
										<i class="pb-heroicons pb-heroicons-book-open"></i>
										<span>{{ __( 'View book list', 'pressbooks-multi-institution' ) }}</span>
									</a>
								</li>
								<li>
									<a
										href="{!! network_admin_url( $network_analytics_active ? 'users.php?page=pb_network_analytics_userlist' : 'users.php' ) !!}"
									>
										<i class="pb-heroicons pb-heroicons-users"></i>
										<span>{{ __( 'View user list', 'pressbooks-multi-institution' ) }}</span>
									</a>
								</li>
								@if( $network_analytics_active )
								<li>
									<a
										href="{!! network_admin_url( 'admin.php?page=pb_network_analytics_admin' ) !!}"
									>
										<i class="pb-heroicons dashicons-before dashicons-chart-area"></i>
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
	<div class="pb-dashboard-row">
		<div class="pb-dashboard-panel">
			<div class="pb-dashboard-content">
				<h2>{{ __('Support resources', 'pressbooks-multi-institution') }}</h2>
				<ul class="horizontal">
					<li class="resources" id="pressbooks-guide">
						<a href="https://guide.pressbooks.com" target="_blank">
							<img
								src="{{ PB_PLUGIN_URL . "assets/dist/images/pb-network-guide.png" }}"
								alt=""
							/>
							{{ __('Network manager guide', 'pressbooks-multi-institution' ) }}
						</a>
						<p>{{ __( 'Learn how to administer your Pressbooks network from our comprehensive how-to guide.', 'pressbooks-multi-institution' ) }}</p>
					</li>
					<li class="resources" id="forum">
						<a href="https://pressbooks.community" target="_blank">
							<img
								src="{{ PB_PLUGIN_URL . "assets/dist/images/pb-forum.png" }}"
								alt=""
							/>
							{{ __('Pressbooks community forum', 'pressbooks-multi-institution' )}}
						</a>
						<p>{{ __( 'Discuss issues of interest with other network managers and Pressbooks support staff.', 'pressbooks-multi-institution' ) }}</p>
					</li>
					<li class="resources" id="webinars">
						<a href="https://pressbooks.com/webinars" target="_blank">
							<img
								src="{{ PB_PLUGIN_URL . "assets/dist/images/pb-spotlight-series.png" }}"
								alt=""
							/>
							{{ __('Pressbooks webinars', 'pressbooks-multi-institution' ) }}
						</a>
						<p>{{ __( 'Become a confident Pressbooks user by attending a free, live webinar.', 'pressbooks-multi-institution' ) }}</p>
					</li>
					<li class="resources" id="contact">
						<a href="mailto:premiumsupport@pressbooks.com" target="_blank">
							<img
								src="{{ PB_PLUGIN_URL . "assets/dist/images/pb-contact-support.png" }}"
								alt=""
							/>
							{{ __('Contact Pressbooks Support', 'pressbooks-multi-institution') }}
						</a>
						<p>{{ __( 'Email Pressbooks\' Premium Support team to report bugs or get personalized help.', 'pressbooks-multi-institution' ) }}</p>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
