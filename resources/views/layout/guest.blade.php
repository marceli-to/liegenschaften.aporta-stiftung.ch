@include('partials.header')
<main role="main" class="site">
  <div>
    <section class="mx-auto auth">
      @yield('content')
    </section>
  </div>
</main>
@include('partials.footer')