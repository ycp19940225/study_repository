
### **端口**

nslookup  查找真实地址

iptables -I INPUT -p tcp -m state --state NEW -m tcp --dport 8888 -j ACCEPT
service iptables save

netstat -nltp|grep 80
iptables -I INPUT -p tcp --dport 80 -j ACCEPT


1.使用lsof 命令来查看端口是否开放
lsof -i:1025 //如果有显示说明已经开放了，如果没有显示说明没有开放
lsof(list open files)是一个列出当前系统打开文件的工具。
在linux环境下，任何事物都以文件的形式存在，通过文件不仅仅可以访问常规数据，还可以访问网络连接和硬件。因为 lsof 需要访问核心内存和各种文件，所以必须以 root 用户的身份运行它才能够充分地发挥其功能。
2.使用netstat 命令来查看端口是否开放

//查看是否监听在0.0.0.0:1025
netstat -aptn |grep -i 1025 //a:all_sockets p:process t:tcp n:num 
//查看TCP类型的端口
netstat -lptn |grep -i 1025  //l:listening_sockets  p:process  t:tcp n:num
//查看UDP类型的端口
netstat -lpun |grep -i 1025 //l:listening_sockets  p:process u:udp n:num

 netstat命令用于显示与IP、TCP、UDP和ICMP协议相关的统计数据，一般用于检验本机各端口的网络连接情况。
3. 使用telnet方式测试远程主机端口是否打开
telnet 127.0.0.1 1025//telnet IP 端口号
   Trying 127.0.0.1...
   Connected to 127.0.0.1.
   Escape character is '^]'.



wsl 
sudo su - 

创建目录：mkdir(make directories)

创建文件 touch

webbench -c 10 -t 10 http://test.domain.com/phpinfo.php


### nginx

mysql:重启
service mysqld restart


aapache命令：
/usr/local/apache2/bin/apachectl restart //重启

nginx 重启
/usr/local/nginx/sbin/nginx -t
/usr/local/nginx/sbin/nginx -s reload

nginx -s reload  ：修改配置后重新加载生效
nginx -s reopen  ：重新打开日志文件
nginx -t -c /path/to/nginx.conf 测试nginx配置文件是否正确

关闭nginx：
nginx -s stop  :快速停止nginx
         quit  ：完整有序的停止nginx

其他的停止nginx 方式：

ps -ef | grep nginx

kill -QUIT 主进程号     ：从容停止Nginx
kill -TERM 主进程号     ：快速停止Nginx
pkill -9 nginx          ：强制停止Nginx



启动nginx:
nginx -c /path/to/nginx.conf

平滑重启nginx：
kill -HUP 主进程号

进入共享目录
\\192.168.1.11\share\


无论是否退出 vi，均可保存所做的工作。按 ESC 键，确定 vi 是否处于命令模式。
操作   键入
 
保存，但不退出vi                          :w
 
保存并退出vi                                 :wq
 
退出vi，但不保存更改                   :q!
 
用其他文件名保存                         :w filename
 
在现有文件中保存并覆盖该文件    :w! filename


### linux下文件的复制、移动与删除

一、文件复制命令cp
    命令格式：cp [-adfilprsu] 源文件(source) 目标文件(destination)

二、文件移动命令mv
    命令格式：mv [-fiv] source destination

三、文件删除命令rm
    命令格式：rm [fir] 文件或目录


### vi
vi命令详解 https://blog.csdn.net/cyl101816/article/details/82026678


全选（高亮显示）：按esc后，然后ggvG或者ggVG
全部复制：按esc后，然后ggyG
全部删除：按esc后，然后dG
 
解析：
gg：是让光标移到首行，在vim才有效，vi中无效 
v ： 是进入Visual(可视）模式 
G ：光标移到最后一行 
选中内容以后就可以其他的操作了，比如： 
d  删除选中内容 
y  复制选中内容到0号寄存器 
"+y  复制选中内容到＋寄存器，也就是系统的剪贴板，供其他程序用 