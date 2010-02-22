PHP_ARG_ENABLE(nagios, whether to enable nagios support,
[ --enable-nagios   Enable nagios support])

if test "$PHP_NAGIOS" = "yes"; then
  AC_DEFINE(HAVE_NAGIOS, 1, [Whether you have Nagios Extension])
  PHP_NEW_EXTENSION(nagios, nagios.c, $ext_shared)
fi
