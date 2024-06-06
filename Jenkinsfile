pipeline {
    agent any
    environment {
        PROJECT_ID = 'du01-android---wedding'
        CLUSTER_NAME = 'du01-test-deploy'
        LOCATION = 'us-central1-a'
        CREDENTIALS_ID = '2c23a60c-36cb-42e0-b94e-919ed740a19d'
        DOCKER_HUB_USERNAME = 'hungnv93'
        DOCKER_HUB_REPOSITORY_NAME = 'hello'
    }
    stages {
        stage("Checkout code") {
            steps {
                checkout scm
            }
        }
        stage('Check Modified Directories') {
            steps {
                script {
                    // Lấy danh sách các thư mục được thay đổi từ Git
                    def changedDirectories = sh(script: "git diff --name-only HEAD^ HEAD", returnStdout: true).trim().split('\n').collect { it.split('/')[0] as String }.unique()
                    echo "Changed directories: ${changedDirectories}"

                    // Kiểm tra xem thư mục "public" có trong danh sách thư mục được thay đổi hay không
                    def containsPublicDirectory = changedDirectories.any { it == 'public' }

                    // Nếu thư mục "public" được thay đổi, chạy Build image 1, ngược lại chạy Build image 2
                    if (containsPublicDirectory) {
                        echo "Thư mục 'public' đã được thay đổi, chạy Build image 1"
                        buildImage1()
                    } else {
                        echo "Thư mục 'public' không được thay đổi, chạy Build image 2"
                        
                    }
                }
            }
        }
        
        

        // stage('Deploying App to Kubernetes') {
        //     steps {
        //         script {
        //         kubernetesDeploy(configs: "deployment.yml", kubeconfigId: "kubernetes")
        //         }
        //     }
        // }
    }    
}

// Hàm để xây dựng Docker image 1
def buildImage1() {
    stage("Build image 1") {
        steps {
            script {
                echo "Building Docker image: ${env.DOCKER_HUB_USERNAME}/${env.DOCKER_HUB_REPOSITORY_NAME}:${env.BUILD_ID}"
                try {
                    myapp = docker.build("${env.DOCKER_HUB_USERNAME}/${env.DOCKER_HUB_REPOSITORY_NAME}:${env.BUILD_ID}")
                } catch (Exception e) {
                    echo "Failed to build Docker image: ${e.getMessage()}"
                    error("Build failed")
                }
            }
        }
    }
    stage("Push image") {
            steps {
                script {
                    docker.withRegistry('https://registry.hub.docker.com', 'dockerID') {
                        myapp.push("latest")
                        myapp.push("${env.BUILD_ID}")
                    }
                }
            }
        }        
        stage('Deploy to GKE') {
            steps {
                sh "sed -i 's/hello:latest/hello:${env.BUILD_ID}/g' deployment.yaml"
                step([$class: 'KubernetesEngineBuilder', projectId: env.PROJECT_ID, clusterName: env.CLUSTER_NAME, location: env.LOCATION, manifestPattern: 'deployment.yaml', credentialsId: env.CREDENTIALS_ID, verifyDeployments: true])
            }
        }
}
