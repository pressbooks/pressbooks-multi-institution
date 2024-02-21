<div class="wrap">
	<div class="pb-dashboard-row">
		<div class="pb-dashboard-panel">
			<div class="pb-dashboard-content">
				<h1 class="screen-reader-text">{{ __('Institutional Manager Dashboard', 'pressbooks')  }}</h1>
				<h2>
					{{ __( 'Welcome to', 'pressbooks' ) }}
					<span class="network-title">{!! $network_name !!}</span>
				</h2>
				<a class="visit-homepage" href="{{ $network_url }}">
					{{ __( 'Visit network homepage', 'pressbooks' ) }}
				</a>
			</div>
		</div>
	</div>
	<div class="pb-dashboard-row">
			<div class="pb-dashboard-grid">
				<div class="pb-dashboard-panel">
					<div class="pb-dashboard-content">
							<h2>{{ __( 'Institutional Usage', 'pressbooks' ) }}</h2>
							<p style="text-align:center;">
								{!! sprintf( __( '%s has %s books and %s users. ', 'pressbooks' ), "{$institution_name}","<strong>{$total_books}</strong>", "<strong>{$total_users}</strong>" ) !!}
							</p>
							<div class="pb-dashboard-action">
								@if( $network_analytics_active )
									<a
										class="button button-primary"
										href="{!! network_admin_url( 'admin.php?page=pb_network_analytics_admin' ) !!}"
									>
										{{ __( 'Explore stats', 'pressbooks' ) }}
									</a>
							@endif

							</div>
					</div>
				</div>

				<div class="pb-dashboard-panel">
					<div class="pb-dashboard-content">
						<h2>{{ __( 'Administer Institution', 'pressbooks' ) }}</h2>

						<div class="pb-dashboard-flex">
							<img
								class="pb-dashboard-flex-image"
								src="{{ PB_PLUGIN_URL . "assets/dist/images/pb-network-settings.png" }}"
								alt="{{ __( 'Administer network art', 'pressbooks' ) }}"
							/>

							<ul class="actions">
								<li>
									<a
										href="{!! network_admin_url( $network_analytics_active ? 'sites.php?page=pb_network_analytics_booklist' : 'sites.php' ) !!}"
									>
										<i class="pb-heroicons pb-heroicons-book-open"></i>
										<span>{{ __( 'View book list', 'pressbooks' ) }}</span>
									</a>
								</li>
								<li>
									<a
										href="{!! network_admin_url( $network_analytics_active ? 'users.php?page=pb_network_analytics_userlist' : 'users.php' ) !!}"
									>
										<i class="pb-heroicons pb-heroicons-users"></i>
										<span>{{ __( 'View user list', 'pressbooks' ) }}</span>
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	<div class="pb-dashboard-row">
		<div class="pb-dashboard-panel">
			<div class="pb-dashboard-content">
				<h2>{{ __('Support resources', 'pressbooks') }}</h2>
				{{-- TODO: add link to new YouTube playlist. --}}
				<ul class="horizontal">
					<li class="resources" id="getting-started">
						<a href="https://youtube.com/playlist?list=PLMFmJu3NJhevTbp5XAbdif8OloNhqOw5n" target="_blank">
							<img
								src="{{ PB_PLUGIN_URL . "assets/dist/images/pb-getting-started.png" }}"
								alt=""
							/>
							{{ __('Getting started with Pressbooks', 'pressbooks' )}}
						</a>
						<p>{{ __( 'Watch a short video series on how to get started with Pressbooks.', 'pressbooks' ) }}</p>
					</li>
					<li class="resources" id="pressbooks-guide">
						<a href="https://guide.pressbooks.com" target="_blank">
							<img
								src="{{ PB_PLUGIN_URL . "assets/dist/images/pb-guide.png" }}"
								alt=""
							/>
							{{ __('Pressbooks user guide', 'pressbooks' )}}
						</a>
						<p>{{ __( 'Find help and how-tos for your publishing project in this detailed handbook.', 'pressbooks' ) }}</p>
					</li>
					<li class="resources" id="forum">
						<a href="https://pressbooks.community" target="_blank">
							<img
								src="{{ PB_PLUGIN_URL . "assets/dist/images/pb-forum.png" }}"
								alt=""
							/>
							{{ __('Pressbooks community forum', 'pressbooks' ) }}
						</a>
						<p>{{ __( 'Discuss Pressbooks related questions with other users in our public forum.', 'pressbooks' ) }}</p>
					</li>
					<li class="resources" id="webinars">
						<a href="https://pressbooks.com/webinars" target="_blank">
							<img
								src="{{ PB_PLUGIN_URL . "assets/dist/images/pb-webinars.png" }}"
								alt=""
							/>
							{{ __('Pressbooks training webinars', 'pressbooks') }}
						</a>
						<p>{{ __( 'Register for free webinars to learn about Pressbooks features and best practices.', 'pressbooks' ) }}</p>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
