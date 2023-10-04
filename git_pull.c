#include <stdlib.h>
#include <unistd.h>
#include <stdio.h>

#define PI_UID 1000 // for security reasons, I decided to hardcode the uid at this point

int main(int argc, char** argv){
	if(argc != 2){
		fprintf(stderr, "usage: git_pull <path of git repo>\n");
		return 12;
	}

	if(setuid(PI_UID)){
		fprintf(stderr, "cannot change uid\n");
		return 12;
	}

	// change directory
	if(chdir(argv[1])){
		fprintf(stderr, "cannot change dir\n");
		return 12;
	}

	// exec git pull
	system("/usr/bin/git pull");
	
	return 0;
}
