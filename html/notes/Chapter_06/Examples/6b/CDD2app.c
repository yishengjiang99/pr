/*  CDD2app.c */

#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <sys/ioctl.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <stdio.h>
#include <errno.h>

#define CMD1 1
#define CMD2 2
#define MYNUM 0x88888888
#define MYSTR "Eureka!"
#define MYSTR2 " Hello World!"
#define LONGSTR "This is a long string! ABCDEFGHIJKLMNOPQRSTUVWXYZ 0123456789"

main() {
	int fd, len, wlen;
	char str[128];
	int num, rnum;

	strcpy(str, MYSTR);

	// open 
	if((fd = open("/dev/CDD2/CDD2_a", O_RDWR | O_APPEND)) == -1) {
		fprintf(stderr,"ERR:on open():%s\n",strerror(errno));
		exit(0);
	}

	// write 
	wlen = strlen(str);
	if ((len = write(fd, str, wlen)) == -1) {
		fprintf(stderr,"ERR:on write():%s\n",strerror(errno));
		exit(1);
	}

	// read 
	if ((len = read(fd, str, sizeof(str))) == -1) {
		fprintf(stderr,"ERR:on read():%s\n",strerror(errno));
		exit(1);
	}
	fprintf(stdout, "%s\n", str);

	// write 
	wlen = strlen(str);
	if ((len = write(fd, str, wlen)) == -1) {
		fprintf(stderr,"ERR:on write():%s\n",strerror(errno));
		exit(1);
	}

	// write2 
	strcpy(str, MYSTR2);
	wlen = strlen(str);
	if ((len = write(fd, str, wlen)) == -1) {
		fprintf(stderr,"ERR:on write():%s\n",strerror(errno));
		exit(1);
	}
	// read 
	if ((len = read(fd, str, sizeof(str))) == -1) {
		fprintf(stderr,"ERR:on read():%s\n",strerror(errno));
		exit(1);
	}
	fprintf(stdout, "%s\n", str);

	// write2 
	strcpy(str, LONGSTR);
	wlen = strlen(str);
	if ((len = write(fd, str, wlen)) == -1) {
		fprintf(stderr,"ERR:on write():%s\n",strerror(errno));
		exit(1);
	}

	// lseek()  .. -ve offset from start of file
	if(lseek(fd, -999, SEEK_SET) <0) {		// -ve test
	// if(lseek(fd, -999, SEEK_CUR) <0) {	// -ve test
	// if(lseek(fd, -999, SEEK_END) <0) {	// -ve test
		fprintf(stderr,"ERR:on lseek():%s\n",strerror(errno));
	}

	// lseek()  .. +ve offset beyond end of file
	if(lseek(fd, 999, SEEK_SET) <0) {		// -ve test
	// if(lseek(fd, 999, SEEK_CUR) <0) {	// -ve test
	// if(lseek(fd, 999, SEEK_END) <0) {	// -ve test
		fprintf(stderr,"ERR:on lseek():%s\n",strerror(errno));
	}

	// lseek()  .. +test for -ve offset 
	// if(lseek(fd,-1, SEEK_SET) <0) {		// -ve test
	// if(lseek(fd,-1, SEEK_CUR) <0) {		
	// if(lseek(fd,0, SEEK_END) <0) {			// +ve test
	if(lseek(fd,-10, SEEK_END) <0) {			// +ve test
		fprintf(stderr,"ERR:on lseek():%s\n",strerror(errno));
	}

	// read 
	if ((len = read(fd, str, sizeof(str))) == -1) {
		fprintf(stderr,"ERR:on read():%s\n",strerror(errno));
		exit(1);
	}
	fprintf(stdout, "%s\n", str);

	// write2 
	strcpy(str, LONGSTR);
	wlen = strlen(str);
	if ((len = write(fd, str, wlen)) == -1) {
		fprintf(stderr,"ERR:on write():%s\n",strerror(errno));
		exit(1);
	}

	// lseek()  .. +test for +ve offset 
	if(lseek(fd,15, SEEK_SET) <0) {			// +ve test
	// if(lseek(fd,15, SEEK_CUR) <0) {
	// if(lseek(fd,15, SEEK_END) <0) {		// -ve test
		fprintf(stderr,"ERR:on lseek():%s\n",strerror(errno));
	}

	// read 
	if ((len = read(fd, str, sizeof(str))) == -1) {
		fprintf(stderr,"ERR:on read():%s\n",strerror(errno));
		exit(1);
	}
	fprintf(stdout, "%s\n", str);

	close(fd);
}
