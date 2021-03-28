#!/bin/bash

#locate the file with concepts and times
ctimes=$1
IFS= read -r  first < "$ctimes"


#read in first line before looping
IFS=$'\t' read -r -a tempVals <<< "$first"
temp_c="temp_$ctimes"

sed -e '1d' "$ctimes" > "$temp_c" 
rm -rf ctimes.txt
#create full length snippet data: take the previous line (in the first iteration its the line grabbed above) and add to it the filename and time of the current snippet data so that each line will have the variables: concept_name start_file start_time end_file end_time. Output these lines to a temp file 
while read myline; do
	IFS=$'\t' read -r -a currentVals <<< "$myline"
	echo "${tempVals[1]} 	${tempVals[0]} 	${tempVals[2]} 	${currentVals[0]} 	${currentVals[2]}" >> ctimes.txt
	
	tempVals=("${currentVals[@]}")
done < "$temp_c"

#create full length snippet data for the last line as logic of loop ends on second to last line
last=$( tail -n -1 "$temp_c")
IFS=$'\t' read -r -a lastVals <<< "$last"
duration="$(ffprobe -i ${lastVals[0]} -show_entries format=duration -v quiet -of csv="p=0")"
#because there is no next concept to inform end time, put special case "end" which signals end of file
echo "${lastVals[1]}	${lastVals[0]}	${lastVals[2]}	${lastVals[0]}	end" >> ctimes.txt 


#create video snippets for each concept
while read myline; do
	IFS=$'\t' read -r -a timeVals <<< "$myline"

	#if the start and end files are the same then only one video snippet
	if [[ ${timeVals[1]} == ${timeVals[3]} ]]; then
		#if end of concepts, then just clip until end of video
		if [[ ${timeVals[4]} == "end" ]]; then
			echo "ffmpeg -i "${timeVals[1]}" -ss "${timeVals[2]}" -c copy "output/${timeVals[0]% }.mp4""
			ffmpeg -i "${timeVals[1]}" -ss "${timeVals[2]}" -c copy "output/${timeVals[0]% }.mp4" > /dev/null 2>&1
		else
			#otherwise clip until start of next concept
			echo "ffmpeg -i "${timeVals[1]% }" -ss "${timeVals[2]}" -to "${timeVals[4]}"  -c copy "output/${timeVals[0]% }.mp4""
			ffmpeg  -i "${timeVals[1]% }" -ss "${timeVals[2]}" -to "${timeVals[4]}"  -c copy "output/${timeVals[0]% }.mp4" > /dev/null 2>&1
		fi
	else
		#clip the end of the video
		echo "ffmpeg -i "${timeVals[1]% }" -ss "${timeVals[2]}" "output/${timeVals[0]% }1.mp4""
		ffmpeg -i "${timeVals[1]% }" -ss "${timeVals[2]}" "output/${timeVals[0]% }1.mp4" > /dev/null 2>&1

		#checking if end time is end of video, if not then clip the beginning of next video
		if [[ ${timeVals[4]} != "00:00:00" ]]; then
			echo "ffmpeg -i "${timeVals[3]% }" -ss 00:00:00 -to "${timeVals[4]}" "output/${timeVals[0]% }2.mp4""
			ffmpeg -i "${timeVals[3]% }" -ss 00:00:00 -to "${timeVals[4]}" "output/${timeVals[0]% }2.mp4" > /dev/null 2>&1
		fi
	fi
done < ctimes.txt


