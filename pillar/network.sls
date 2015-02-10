# Pillar data for various network settings.
#
# Two nameservers should be specified
network:
  location: local
  gateway_ip: 10.10.50.1
  nameservers: |
    nameserver 8.8.8.8
    nameserver 8.8.4.4

php-config:
  post_max_size: 200M
  upload_max_filesize: 200M
  pm_max_children: 5
  pm_start_servers: 2
  pm_min_spare_servers: 1
  pm_max_spare_servers: 3

wsuwp-config:
  primary_host: wp.wsu.dev
  primary_email: admin@wp.wsu.dev
  primary_user: admin
  primary_pass: password
  database: wsuwp
  db_user: wp
  db_pass: wp
  db_host: 127.0.0.1
  cache_key: wsuwp
  batcache: true
  default_theme: spine
  nonces: |
    define('AUTH_KEY',         '$Aj=$}MK[fb=!aw:|K*^?1DU#?P|;nPC8w7W+lk-th$4].W-]K)p-A8:vbjR?:Ux');
    define('SECURE_AUTH_KEY',  'UhQf+zI;4IYmv9rcm>. _Z^GXXDT56nKPqv$/+x6,Ckse}&g1e$~_YtezqAhj-ZD');
    define('LOGGED_IN_KEY',    'j|C8,6f6Bh3+zt-hm|N(XvABv#?N&|C7rzD4KSyKY<2~XsvZ}KChH$d EA~,wi|b');
    define('NONCE_KEY',        '!AJT@YB20t|dD}N/F&3%KT&Riy]h#]DxLEP^uwwJRgMYQ;9xZg@Vm,H1l^7$C_G]');
    define('AUTH_SALT',        'f -04)xVh{8|&xacUCAY+_4%U|U-G3)%KWn4.Us{M!_}^,-38KR@S4Iwcv$U^V6[');
    define('SECURE_AUTH_SALT', 'mD</>Nt*~?f.eK}2-j->})II6b^Rrh+0L-k_0+b~ #BNdy8tJI!19v;+%G_:|Bm{');
    define('LOGGED_IN_SALT',   'e;OF=3H<U~+_j:Th]A<WDNT2?3+`6q +=#8UBN#mbPv]5Cy(!p:gq-pp^p>D4JCm');
    define('NONCE_SALT',       '-acH8ua>B89>U-TQ3)<1b1]8h:eV`Oywx,4l0C^8Hr-BJw2=2D4q-al[NElC/+fP');
