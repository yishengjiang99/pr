KDIR := /lib/modules/$(shell uname -r)/build
PWD := $(shell pwd)

CDDparm := 0
CDDparm := $(CDDparm)

obj-m := CDD2.o

all: clean run runappend
	ls -l /dev/CDD2/CDD2_*;
	@make -s clean

run: CDD2 CDD2app runproc
	echo "Hello World!" > /dev/CDD2/CDD2_a;
	echo "Hello Universe!" > /dev/CDD2/CDD2_b;
	echo "Hello Galaxy!" > /dev/CDD2/CDD2_c;
	cat < /dev/CDD2/CDD2_a;
	cat < /dev/CDD2/CDD2_b;
	cat < /dev/CDD2/CDD2_c;
	echo "Hello Galaxy!" > /dev/CDD2/CDD2_a;
	cat < /dev/CDD2/CDD2_a;
	./CDD2app;
	ls -l /dev/CDD2/CDD2_?

runproc:
	cat < /proc/myCDD/CDD2;
	echo "1234" > /proc/myCDD/CDD2;
	cat < /proc/myCDD/CDD2;
	cat < /proc/myCDD/CDD2;

runappend: CDD2 CDD2app
	echo "Hello World!" > /dev/CDD2/CDD2_a;
	echo "Hello Universe!" > /dev/CDD2/CDD2_b;
	echo "Hello Galaxy!" >> /dev/CDD2/CDD2_b;
	cat < /dev/CDD2/CDD2_a;
	cat < /dev/CDD2/CDD2_b;
	cat < /dev/CDD2/CDD2_c;

load: CDD2.o 
	-su -c "insmod ./CDD2.ko CDDparm=$(CDDparm);";

CDD2: load 
	-su -c "mkdir /dev/CDD2/; \
		mknod -m 666 /dev/CDD2/CDD2_a c $(shell grep CDD2 /proc/devices | sed 's/CDD2//') 0; \
		mknod -m 666 /dev/CDD2/CDD2_b c $(shell grep CDD2 /proc/devices | sed 's/CDD2//') 1; \
		mknod -m 666 /dev/CDD2/CDD2_c c $(shell grep CDD2 /proc/devices | sed 's/CDD2//') 2;"


CDD2.o:
	$(MAKE) -C $(KDIR) M=$(PWD) modules

###
###  Alternatively, you may want to use the early 2.6 syntax of
###  $(MAKE) -C $(KDIR) SUBDIRS=$(PWD) modules
###

CDD2app:
	-gcc -o CDD2app CDD2app.c;

unload:
	-su -c "rmmod CDD2; rm -fr /dev/CDD2;"

clean: unload
	-@rm -fr *.o CDD2*.o CDD2*.ko .CDD2*.* CDD2*.*.* CDD2app .tmp_versions [mM]odule*


mem: 
	ls -l /dev | egrep '^c' | egrep ' 1,'  | sort +5.0n -6.0
