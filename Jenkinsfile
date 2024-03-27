pipeline {
    agent any
    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }
	    stage('Setup') {
	        steps {
	            sh 'php -v'
                sh 'composer --version'
            }
        }
        stage('Execute SonarQube scan') {
            agent { label '!windows' }
            steps {
                echo 'Executing SonarQube scan'
                script {
                    scannerHome = tool 'SonarQube Scanner'
                }
                withSonarQubeEnv('SonarQube_Hive') {
                    sh "${scannerHome}/bin/sonar-scanner -Dsonar.projectKey=sqp_0372b2f63613590516a6fd678db64083e4a1f43a"
                }
            }
        }
        stage('Execute PHPUnit Tests') {
            steps {
                script {
                    // Run PHPUnit tests
                    sh 'chmod ./vendor/bin/phpunit'
                }
            }
        }
    }
    post {
        always {
            echo 'Done'
            phpunit '--log-junit report.xml'
        }
        success {
            echo "Build successful"
        }
        failure {
            echo "Build failed, see console for the details"
        }
    }
}